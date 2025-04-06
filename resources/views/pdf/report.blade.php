<!DOCTYPE html>
<html>

<head>
    <title>Monthly Report</title>
    <style>
        @page {
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            page-break-before: always;
        }

        header {
            text-align: center;
            line-height: 1.5;
            margin-bottom: 10px;
            padding: 10px 0;
        }

        header img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-right: 10px;
        }

        header .info {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
            padding-left: 10px;
        }

        .content {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        /* Page break */
        .page-break {
            page-break-before: always;
        }

        /* Ensure the content doesn't overflow */
        .report-content {
            overflow: hidden;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    @include('pdf.partials.regional-office-header', ['selectedDate' => $selectedDate ?? now()])

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>OR Number</th>
                <th>Booked By</th>
                <th>Number of Guests</th>
                <th>Number of Days</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
            <tr>
                <td>{{ $report['date'] }}</td>
                <td>{{ $report['orNumber'] }}</td>
                <td>{{ $report['bookedBy'] }}</td>
                <td>{{ $report['numberOfGuests'] }}</td>
                <td>{{ $report['numberOfDays'] }}</td>
                <td>{{ number_format($report['amount'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="italic text-center">No records found.</td>
            </tr>
            @endforelse

            <tr>
                <td colspan="5" class="font-bold text-right">Total Amount:</td>
                <td class="font-bold">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>