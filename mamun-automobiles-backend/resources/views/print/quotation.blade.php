<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $type ?? 'Invoice' }} - {{ $invoice['invoice_number'] ?? 'N/A' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'sans-serif';
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .bismillah {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #1a4d98;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .header-left {
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 5px;
        }
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a4d98;
            margin-bottom: 2px;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #666;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a4d98;
            margin-bottom: 2px;
        }
        .company-slogan {
            font-size: 12px;
            color: #1a4d98;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 10px;
            color: #555;
            line-height: 1.4;
        }
        .thick-divider {
            border-top: 2px solid #1a4d98;
            margin: 10px 0;
        }
        .meta-bar {
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 8px 10px;
            margin-bottom: 15px;
            font-size: 11px;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
        }
        .meta-table td {
            vertical-align: middle;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .customer-info {
            vertical-align: top;
            width: 60%;
        }
        .customer-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        .vehicle-info {
            vertical-align: top;
            width: 40%;
            text-align: right;
        }
        .vehicle-no {
            font-size: 18px;
            font-weight: bold;
            color: #1a4d98;
            margin-bottom: 3px;
        }
        .verified-text {
            font-size: 10px;
            color: #28a745;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            position: relative;
        }
        .items-table th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            color: #1a4d98;
        }
        .items-table th.text-left { text-align: left; }
        .items-table th.text-right { text-align: right; }
        
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: middle;
        }
        .items-table td.text-center { text-align: center; }
        .items-table td.text-right { text-align: right; }
        
        .totals-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .totals-table {
            width: 35%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 8px;
            text-align: right;
        }
        .totals-table .label {
            color: #1a4d98;
            text-align: left;
        }
        .total-row {
            font-weight: bold;
            font-size: 13px;
            color: #1a4d98;
        }
        
        .in-words-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
            font-size: 11px;
            clear: both;
        }
        
        .signatures-container {
            width: 100%;
            margin-top: 50px;
            margin-bottom: 30px;
        }
        .sig-box {
            width: 30%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
        }
        
        .footer-container {
            width: 100%;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 20px;
        }
        .footer-notes {
            width: 45%;
            float: left;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .footer-notes-title {
            font-weight: bold;
            color: #1a4d98;
            margin-bottom: 4px;
            font-size: 10px;
        }
        .footer-notes-content {
            font-size: 9px;
            line-height: 1.4;
        }
        .footer-dua {
            width: 45%;
            float: right;
            text-align: right;
        }
        .dua-title {
            font-weight: bold;
            color: #1a4d98;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .dua-content {
            font-size: 9px;
            font-style: italic;
            color: #555;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: -1;
            width: 400px;
        }
    </style>
</head>
<body>

    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم</div>

    <table class="header-table">
        <tr>
            <td class="header-left">
                <!-- Replace with dynamic logo if available -->
                <div style="font-size:24px; font-weight:bold; color:#d9232d; font-style:italic; margin-bottom:10px;">
                    MAMUN <span style="color:#000; font-size:12px;">AUTOMOBILES</span>
                </div>
                <div class="doc-title">{{ $type ?? 'Quotation Rate' }}</div>
                <div class="doc-subtitle">Mamun Automobiles // Premium service {{ strtolower($type ?? 'quote') }}</div>
                <div class="doc-subtitle">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
            <td class="header-right">
                <div class="company-name">Mamun Automobiles</div>
                <div class="company-slogan">Premium Auto Care</div>
                <div class="company-info">
                    Plot # 197, Road # 13, Sector # 10, Uttara, Dhaka-1230<br>
                    Hotline: 01812238820, 01712524779
                </div>
            </td>
        </tr>
    </table>

    <div class="thick-divider"></div>

    <div class="meta-bar">
        <table class="meta-table">
            <tr>
                <td width="33%">Date: <span style="font-weight:normal;">{{ $invoice['date'] ?? date('d M Y') }}</span></td>
                <td width="34%" style="text-align:center;">
                    {{ isset($type) && $type === 'Invoice' ? 'Invoice ID' : 'Quote ID' }}: 
                    <span style="font-weight:normal;">{{ $invoice['invoice_number'] ?? 'QT-2026-000' }}</span>
                </td>
                <td width="33%" style="text-align:right;">Vehicle No: <span style="font-weight:normal;">{{ $invoice['vehicle_no'] ?? 'DH-000' }}</span></td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td class="customer-info">
                <div class="customer-name">{{ $invoice['customer_name'] ?? 'Techsoul' }}</div>
                <div style="color:#666;">{{ $invoice['customer_phone'] ?? '+8801839499233' }}</div>
            </td>
            <td class="vehicle-info">
                <div class="vehicle-no">{{ $invoice['vehicle_no'] ?? 'DH-002' }}</div>
                <div class="verified-text">Verified Registration</div>
            </td>
        </tr>
    </table>

    <!-- Watermark (optional) -->
    <!-- <img src="car-watermark.png" class="watermark"> -->

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="50%" class="text-left">Description of Work / Parts</th>
                <th width="10%">Qty</th>
                <th width="15%" class="text-right">Rate</th>
                <th width="20%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($invoice['items']) && count($invoice['items']) > 0)
                @foreach($invoice['items'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">৳ {{ number_format($item['rate'], 2) }}</td>
                    <td class="text-right">৳ {{ number_format($item['amount'], 2) }}</td>
                </tr>
                @endforeach
            @else
                <!-- Demo Data matching the image -->
                <tr>
                    <td class="text-center">1</td>
                    <td>A/C Gas Refill & Service</td>
                    <td class="text-center">1</td>
                    <td class="text-right">৳ 3,000.00</td>
                    <td class="text-right">৳ 3,000.00</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>Engine Oil Change Service</td>
                    <td class="text-center">1</td>
                    <td class="text-right">৳ 1,000.00</td>
                    <td class="text-right">৳ 1,000.00</td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td>Premium synthetic oil for modern engines</td>
                    <td class="text-center">1</td>
                    <td class="text-right">৳ 1,400.00</td>
                    <td class="text-right">৳ 1,400.00</td>
                </tr>
                <tr>
                    <td class="text-center">4</td>
                    <td>High-efficiency oil filter for sedan models</td>
                    <td class="text-center">1</td>
                    <td class="text-right">৳ 549.00</td>
                    <td class="text-right">৳ 549.00</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals-container">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td>৳ {{ number_format($invoice['subtotal'] ?? 5949.00, 2) }}</td>
            </tr>
            <tr>
                <td class="label">VAT:</td>
                <td>৳ {{ number_format($invoice['vat'] ?? 0.00, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total:</td>
                <td>৳ {{ number_format($invoice['total'] ?? 5949.00, 2) }}</td>
            </tr>
        </table>
        <div style="clear:both;"></div>
    </div>

    <div class="in-words-box">
        <strong>In Words:</strong> <i>Amount in Words Placeholder Only</i>
    </div>

    <table class="signatures-container">
        <tr>
            <td width="33%" style="vertical-align:bottom;">
                <div class="sig-box" style="float:left;">
                    Customer Signature
                </div>
            </td>
            <td width="33%" style="vertical-align:bottom;">
                <div class="sig-box" style="margin: 0 auto;">
                    Authorized Sign
                </div>
            </td>
            <td width="34%" style="vertical-align:bottom; text-align:right;">
                @if(isset($qrCode))
                <div style="display:inline-block; text-align:center;">
                    <img src="{{ $qrCode }}" alt="QR Code" width="80" height="80" />
                    <div style="font-size: 9px; font-weight: bold; margin-top: 2px; color: #1a4d98;">Scan to Verify</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer-container">
        <div class="footer-notes">
            <div class="footer-notes-title">বিশেষ দ্রষ্টব্য:</div>
            <div class="footer-notes-content">
                কর্তৃপক্ষ ৩ মাসের বেশি সময় ধরে ফেলে রাখা কোনো মালামাল বা কোনো ধরনের ক্ষতি বা লোকসানের জন্য দায়ী নয়।<br>
                <strong>মেয়াদ:</strong> এই কোটেশনটি ইস্যু করার তারিখ থেকে ১৫ দিনের জন্য বৈধ।
            </div>
        </div>
        <div class="footer-dua">
            <div class="dua-title">সফরের দোয়া:</div>
            <div class="dua-content">
                "বিসমিল্লাহি মাজরেহা ওয়া মুরসাহা, ইন্না রাব্বি লা- গাফুরুর রাহিম।"
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>

</body>
</html>
