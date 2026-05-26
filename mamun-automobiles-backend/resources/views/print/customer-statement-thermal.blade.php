<!DOCTYPE html>
<html>
<head>
    <title>Customer Statement</title>
    <style>
        body { font-family: monospace; font-size: 10px; width: 80mm; margin: 0 auto; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        table { width: 100%; margin-top: 10px; border-bottom: 1px dashed #000; }
        th, td { padding: 2px 0; }
        .bold { font-weight: bold; }
        .divider { border-bottom: 1px dashed #000; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="text-center bold">
        MAMUN AUTOMOBILES<br>
        <span style="font-size:8px; font-weight:normal;">Premium Auto Care</span><br>
        <span style="font-size:8px; font-weight:normal;">01812238820, 01712524779</span>
    </div>
    
    <div class="divider"></div>
    <div>Customer: {{ $customer->name ?? 'N/A' }}</div>
    <div>Phone: {{ $customer->phone ?? 'N/A' }}</div>
    <div>Date: {{ date('d M Y') }}</div>
    <div class="divider"></div>

    <table>
        <tr>
            <th align="left">Date</th>
            <th align="right">Dr</th>
            <th align="right">Cr</th>
            <th align="right">Bal</th>
        </tr>
        @foreach($statement as $row)
        <tr>
            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d/m') }}</td>
            <td class="text-right">{{ number_format($row->debit, 0) }}</td>
            <td class="text-right">{{ number_format($row->credit, 0) }}</td>
            <td class="text-right bold">{{ number_format($row->balance, 0) }}</td>
        </tr>
        @endforeach
    </table>
    
    <div class="text-center" style="margin-top:10px;">
        Closing Balance: {{ number_format($ledger->current_balance ?? 0, 2) }} ৳<br><br>
        @if(isset($qrCode))
        <img src="{{ $qrCode }}" width="60" height="60"><br>
        Scan to Verify
        @endif
        <br><br>Thank You!
    </div>
</body>
</html>
