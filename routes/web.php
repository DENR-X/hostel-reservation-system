<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtendReservationController;
use App\Http\Controllers\GenerateReportController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationAssignBedsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationProcessController;
use App\Http\Controllers\ReservationStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPasswordController;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//* All
Route::get('/', function () {
    $hostels = Office::with('region')->where('has_hostel', true)->get();

    return Inertia::render('LandingPage', [
        'canLogin' => Route::has('login'),
        'hostels' => $hostels
    ]);
});

//* Guest Reservation Process
Route::get('/reservation', [ReservationProcessController::class, 'form'])->name('reservation.form');
Route::post('/reservation', [ReservationProcessController::class, 'create'])->name('reservation.create');
Route::get('/reservation/confirmation', [ReservationProcessController::class, 'confirmation'])->name('reservation.confirmation');
Route::get('/reservation/status', [ReservationStatusController::class, 'checkStatusForm'])->name('reservation.checkStatusForm');
Route::get('/reservation/status/{code}', [ReservationStatusController::class, 'checkStatus'])->name('reservation.checkStatus');

//* Admin Reservation
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'list'])->name('reservation.list');
    Route::put('/reservations/edit-status', [ReservationStatusController::class, 'editStatus'])->name('reservation.editStatus');
    Route::post('/reservations/extend', [ExtendReservationController::class, 'extend'])->name('reservation.extend');
    Route::get('/reservations/{id}/extend', [ExtendReservationController::class, 'extendForm'])->name('reservation.extendForm');
    Route::get('/reservations/{id}/edit-status', [ReservationStatusController::class, 'editStatusForm'])->name('reservation.editStatusForm');
    Route::put('/reservations/{id}/cancel', [ReservationStatusController::class, 'cancel'])->name('reservation.cancel');
    Route::get('/reservations/{id}/edit-bed-assignment', [ReservationAssignBedsController::class, 'editBedAssignmentForm'])->name('reservation.editBedAssignmentForm');
    Route::put('/reservations/edit-assign-bed', [ReservationAssignBedsController::class, 'editAssignBed'])->name('reservation.editAssignBed');
    Route::get('/reservations/{id}/edit-assign-bed', [ReservationAssignBedsController::class, 'editAssignBedForm'])->name('reservation.editAssignBedForm');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservation.show');
});

//* Admin Reservation Payment
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/reservations/payment', [PaymentController::class, 'payment'])->name('reservation.payment');
    Route::post('/reservations/pay-later', [PaymentController::class, 'payLater'])->name('reservation.payLater');
    Route::get('/reservations/payment/{id}/history', [PaymentController::class, 'paymentHistory'])->name('reservation.paymentHistory');
    Route::get('/reservations/payment/{id}', [PaymentController::class, 'paymentForm'])->name('reservation.paymentForm');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/reservations/payment/exempt', [PaymentController::class, 'exemptPayment'])->name('reservation.exemptPayment');
    Route::get('/reservations/payment/{id}/exempt', [PaymentController::class, 'exemptPaymentForm'])->name('reservation.exemptPaymentForm');
});

//* Waiting List Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/waiting-list', [ReservationController::class, 'waitingList'])->name('reservation.waitingList');
    Route::post('/waiting-list/assign-bed', [ReservationAssignBedsController::class, 'assignBeds'])->name('reservation.assignBeds');
    Route::get('/waiting-list/assign-bed/{id}', [ReservationAssignBedsController::class, 'assignBedsForm'])->name('reservation.assignBedsForm');
});

//* Admin Room Management
Route::middleware(['auth', 'verified', 'isSuperAdmin'])->group(function () {
    Route::get('/rooms', [RoomController::class, 'list'])->name('room.list');
    Route::inertia('/rooms/create', 'Admin/Room/CreateRoom')->name('room.createForm');
    Route::post('/rooms/create', [RoomController::class, 'create'])->name('room.create');
    Route::get('/rooms/edit/{id}', [RoomController::class, 'editForm'])->name('room.editForm');
    Route::put('/rooms/edit/{room}', [RoomController::class, 'edit'])->name('room.edit');
    Route::delete('/rooms/{id}', [RoomController::class, 'delete'])->name('room.delete');
});

//* Admin Guest Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/guests', [GuestController::class, 'list'])->name('guest.list');
});

//* Admin analytics and reports
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [GenerateReportController::class, 'list'])->name('reports');
});

//* Admin Notifications
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'list'])->name('notification.list');
    Route::put('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead');
    Route::put('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notification.markAllAsRead');
    Inertia::share('unreadNotificationCount', function () {
        return Auth::user()?->unreadNotifications()?->count();
    });
});

//* Super Admin Office Management
Route::middleware(['auth', 'verified', 'isSuperAdmin'])->group(function () {
    Route::get('/offices', [OfficeController::class, 'list'])->name('office.list');
    Route::get('/offices/form/{id?}', [OfficeController::class, 'upsertForm'])->name('office.upsertForm');
    Route::post('/offices/upsert/{id?}', [OfficeController::class, 'upsert'])->name('office.upsert');
    Route::delete('/offices/{id}', [OfficeController::class, 'delete'])->name('office.delete');
});


//* Super Admin User Management
Route::middleware(['auth', 'verified', 'isSuperAdmin'])->group(function () {
    Route::get('/users', [UserController::class, 'list'])->name('user.list');
    Route::get('/users/create', [UserController::class, 'createForm'])->name('user.createForm');
    Route::post('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::put('/users/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::get('/users/edit/{id}', [UserController::class, 'editForm'])->name('user.editForm');
    Route::put('/users/change-password', [UserPasswordController::class, 'changePass'])->name('user.changePass');
    Route::get('/users/change-password/{id}', [UserPasswordController::class, 'changePassForm'])->name('user.changePassForm');
    Route::delete('/users/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
});

require __DIR__ . '/auth.php';
