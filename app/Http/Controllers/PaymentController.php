<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\PaymentExemption;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function paymentForm($id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Payment/PaymentForm/PaymentForm', [
            'reservation' => $reservation
        ]);
    }


    //make a payment for reservation
    public function payment(Request $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        $validated = $request->validate([
            'reservation_id' => ['required', 'numeric'],
            'or_number' => ['required', 'string', 'unique:payments,or_number'],
            'or_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'online'])],
            'transaction_id' => ['required', 'string', 'unique:payments,transaction_id'],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($reservation) {
                    if ($value > $reservation->remaining_balance) {
                        $fail('The payment amount must not exceed the remaining balance.');
                    }
                }
            ],
        ], [
            'or_number.unique' => 'OR number already existed.',
            'transaction_id.unique' => 'Transaction ID already existed.',
            'amount.min' => 'The payment amount must not be zero or less.',
        ]);

        try {
            DB::transaction(function () use (&$validated, $reservation) {
                $payment = Payment::create([
                    'amount' => $validated['amount'],
                    'or_number' => $validated['or_number'],
                    'or_date' => $validated['or_date'],
                    'transaction_id' => $validated['transaction_id'],
                    'payment_method' => $validated['payment_method'],
                    'reservation_id' => $validated['reservation_id'],
                ]);

                $latestBalance = $reservation->remaining_balance - $payment->amount;

                //Update reservation remaining balance
                $reservation->update(['remaining_balance' => $latestBalance]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => 'Payment processing failed. Please try again.']);
        }

        return to_route('reservation.paymentHistory', ['id' => $validated['reservation_id']])->with('success', 'Successfully record a payment.');
    }
    public function paymentHistory(int $id)
    {
        $reservationPaymentHistory = Reservation::where('id', $id)
            ->where('hostel_office_id', Auth::user()->office_id)
            ->with([
                'payments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->first();

        $exemptedPayments = PaymentExemption::with(['reservation', 'guest', 'user.office'])
            ->where('reservation_id', $id)
            ->get();

        return Inertia::render('Admin/Payment/ReservationPaymentHistory/ReservationPaymentHistory', [
            'reservationPaymentHistory' => $reservationPaymentHistory,
            'exemptedPayments' => $exemptedPayments
        ]);
    }

    public function payLater(Request $request)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($request->id);

        DB::transaction(function () use ($reservation) {
            $reservation->payment_type = 'pay_later';
            $reservation->save();
        });

        return to_route('reservation.show', ['id' => $reservation->id])->with('success', 'Successfully change to pay later.');
    }

    public function exemptPaymentForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->whereNotIn('status', ['checked_out', 'canceled'])
            ->with([
                'guests' => function ($query) {
                    $query->where('is_exempted', false)
                        ->with(['guestBeds.bed.room']);
                }
            ])
            ->findOrFail($id);

        return Inertia::render('Admin/Payment/ExemptPayment/ExemptPaymentForm', [
            'reservation' => $reservation
        ]);
    }

    public function exemptPayment(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'selected_guest_id' => ['required', 'exists:guests,id'],
            'reason' => ['required', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);
                $guest = Guest::whereHas('reservation', function ($query) use ($reservation) {
                    $query->where('id', $reservation->id);
                })->findOrFail($validated['selected_guest_id']);

                $bed = Bed::whereHas('guestBeds', function ($query) use ($reservation, $guest) {
                    $query->where('reservation_id', $reservation->id)
                        ->where('guest_id', $guest->id);
                })->first();

                if (!$bed || !$bed->price) {
                    throw new \Exception('The selected guest does not have an associated bed.');
                }

                PaymentExemption::create([
                    'reservation_id' => $validated['reservation_id'],
                    'price' => $bed->price,
                    'guest_id' => $guest->id,
                    'user_id' => Auth::user()->id,
                    'reason' => $validated['reason'],
                ]);

                $guest->is_exempted = true;
                $guest->save();

                //update the total amount, daily_rate, and remaining balance
                $checkInDate = Carbon::parse($reservation->check_in_date);
                $checkOutDate = Carbon::parse($reservation->check_out_date);
                $lengthOfStay = $checkInDate->diffInDays($checkOutDate, false);

                $totalPayed = Payment::where('reservation_id', $reservation->id)
                    ->get()
                    ->sum('amount');

                // If value is below zero after re computing make the result as zero.
                $newDailyRate = max(0, $reservation->daily_rate - $bed->price);
                $newTotalBillings = $newDailyRate * $lengthOfStay;
                $newRemainingBalance = max(0, $newTotalBillings - $totalPayed);

                $reservation->update([
                    'daily_rate' => $newDailyRate,
                    'total_billings' => $newTotalBillings,
                    'remaining_balance' => $newRemainingBalance
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        return redirect()->route('reservation.show', [
            'id' => $validated['reservation_id']
        ]);
    }
}
