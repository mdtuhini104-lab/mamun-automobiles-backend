<!DOCTYPE html>
<html>
<head>
    <title>Customer Statement</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 30px; }
        .header h2 { margin: 0; color: #1a4d98; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>MAMUN AUTOMOBILES</h2>
        <p>Plot # 197, Road # 13, Sector # 10, Uttara, Dhaka-1230</p>
        <h3>Customer Statement</h3>
        <p><strong>Customer:</strong> {{ $customer->name ?? 'N/A' }} <br>
           <strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }} <br>
           <strong>Date:</strong> {{ date('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-right">Debit (৳)</th>
                <th class="text-right">Credit (৳)</th>
                <th class="text-right">Balance (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statement as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
                <td>{{ $row->note }}</td>
                <td class="text-right">{{ number_format($row->debit, 2) }}</td>
                <td class="text-right">{{ number_format($row->credit, 2) }}</td>
                <td class="text-right">{{ number_format($row->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated statement and does not require a signature.</p>
        @if(isset($qrCode))
        <img src="{{ $qrCode }}" width="80" height="80">
        @endif
    </div>
</body>
</html>
