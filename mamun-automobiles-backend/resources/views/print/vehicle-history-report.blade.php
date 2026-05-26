<!DOCTYPE html>
<html>
<head>
    <title>Vehicle History Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .header { margin-bottom: 30px; }
        .header h2 { margin: 0; color: #1a4d98; }
        .meta-table { width: 100%; border: none; margin-bottom: 20px; }
        .meta-table td { border: none; padding: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>MAMUN AUTOMOBILES</h2>
        <p>Plot # 197, Road # 13, Sector # 10, Uttara, Dhaka-1230</p>
        <h3>Vehicle Service History Report</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Registration No:</strong> {{ $vehicle->registration_number ?? 'N/A' }}</td>
            <td><strong>Make & Model:</strong> {{ $vehicle->make ?? '' }} {{ $vehicle->model ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Owner:</strong> {{ $customer->name ?? 'N/A' }}</td>
            <td><strong>Report Date:</strong> {{ date('d M Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Mileage</th>
                <th>Services Done</th>
                <th>Parts Replaced</th>
                <th>Mechanic</th>
                <th>Cost (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->service_date)->format('d M Y') }}</td>
                <td>{{ $record->mileage ?? 'N/A' }}</td>
                <td>{{ $record->services_done }}</td>
                <td>{{ $record->parts_changed }}</td>
                <td>{{ $record->mechanic_name }}</td>
                <td>{{ number_format($record->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
