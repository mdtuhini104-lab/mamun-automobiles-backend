<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thermal Receipt - {{ $invoice['invoice_number'] }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0 auto;
            width: {{ $thermal_width }};
            max-width: {{ $thermal_width }};
            background-color: #fff;
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 2px 0; }
        
        .qr-container {
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="text-center mb-2">
        <h2 style="margin: 0; font-size: 16px;" class="font-bold">{{ $company_name }}</h2>
        <p style="margin: 2px 0; font-size: 10px;">123 Workshop Road, Dhaka</p>
        <p style="margin: 2px 0; font-size: 10px;">Tel: +880 1234 567890</p>
    </div>

    <div class="divider"></div>

    <table style="margin-bottom: 5px;">
        <tr>
            <td class="text-left font-bold">INV:</td>
            <td class="text-right">{{ $invoice['invoice_number'] }}</td>
        </tr>
        <tr>
            <td class="text-left font-bold">Date:</td>
            <td class="text-right">{{ $invoice['date'] }}</td>
        </tr>
        <tr>
            <td class="text-left font-bold">Cust:</td>
            <td class="text-right">{{ $invoice['customer_name'] }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th class="text-left" width="50%">Item</th>
                <th class="text-center" width="15%">Qty</th>
                <th class="text-right" width="35%">Amt</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">Gen. Service</td>
                <td class="text-center">1</td>
                <td class="text-right">3000</td>
            </tr>
            <tr>
                <td class="text-left">Engine Oil</td>
                <td class="text-center">2</td>
                <td class="text-right">2000</td>
            </tr>
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="text-left font-bold">Subtotal</td>
            <td class="text-right">5000</td>
        </tr>
        <tr>
            <td class="text-left font-bold">Tax</td>
            <td class="text-right">0</td>
        </tr>
        <tr>
            <td class="text-left font-bold" style="font-size: 14px;">TOTAL</td>
            <td class="text-right font-bold" style="font-size: 14px;">{{ $invoice['total'] }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="qr-container">
        <img src="{{ $qrCode }}" alt="QR" width="100" height="100" />
    </div>

    <div class="text-center mt-2" style="font-size: 10px;">
        Thank you for your business!<br>
        Please visit again.
    </div>

    <!-- Auto cut command for thermal printers when raw printed -->
    <!-- \x1B\x69 -->
</body>
</html>
