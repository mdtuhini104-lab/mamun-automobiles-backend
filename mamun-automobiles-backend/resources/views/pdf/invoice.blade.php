<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #003399;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table h1 {
            color: #003399;
            margin: 0;
            font-size: 28px;
        }
        .company-info {
            text-align: right;
            font-size: 12px;
            color: #666;
        }
        .details-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .customer-details {
            width: 50%;
            vertical-align: top;
        }
        .customer-details h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .invoice-meta {
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .invoice-meta table {
            width: auto;
            float: right;
            border-collapse: collapse;
        }
        .invoice-meta td {
            padding: 4px 5px;
            font-size: 12px;
        }
        .invoice-meta td.label {
            color: #666;
            text-align: left;
        }
        .invoice-meta td.value {
            font-weight: bold;
            text-align: right;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #003399;
            color: #fff;
            text-align: left;
            padding: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e1e1e1;
            font-size: 12px;
        }
        .items-table td.number {
            text-align: right;
        }
        .totals-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .totals-container td {
            vertical-align: top;
        }
        .totals-spacer {
            width: 60%;
        }
        .totals {
            width: 40%;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 6px 5px;
            font-size: 12px;
        }
        .totals td.label {
            color: #666;
            text-align: left;
        }
        .totals td.value {
            font-weight: bold;
            text-align: right;
        }
        .totals tr.grand-total td {
            border-top: 2px solid #003399;
            font-size: 16px;
            font-weight: bold;
            color: #003399;
            padding-top: 10px;
        }
        .footer {
            clear: both;
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 40px;
            border-top: 1px solid #e1e1e1;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            color: #fff;
        }
        .badge-paid { background-color: #28a745; }
        .badge-due { background-color: #dc3545; }
        .badge-partial { background-color: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <h1>INVOICE</h1>
                    <span class="badge badge-{{ strtolower($invoice->payment_status) }}">
                        {{ $invoice->payment_status }}
                    </span>
                </td>
                <td class="company-info">
                    <strong>Mamun Automobiles</strong><br>
                    123 Main Street, Dhaka<br>
                    Phone: +880123456789<br>
                    Email: info@mamunauto.com
                </td>
            </tr>
        </table>

        <table class="details-table">
            <tr>
                <td class="customer-details">
                    <h3>Bill To:</h3>
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    Phone: {{ $invoice->customer->phone }}<br>
                    @if($invoice->customer->address)
                        Address: {{ $invoice->customer->address }}<br>
                    @endif
                </td>
                <td class="invoice-meta">
                    <table>
                        <tr>
                            <td class="label">Invoice Number:</td>
                            <td class="value">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">Date:</td>
                            <td class="value">{{ $invoice->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @if($invoice->job_card_id)
                        <tr>
                            <td class="label">Job Card:</td>
                            <td class="value">#{{ $invoice->job_card_id }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="number">Qty</th>
                    <th class="number">Unit Price</th>
                    <th class="number">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="number">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-container">
            <tr>
                <td class="totals-spacer"></td>
                <td class="totals">
                    <table>
                        <tr>
                            <td class="label">Parts Total:</td>
                            <td class="value">{{ number_format($invoice->parts_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Service Total:</td>
                            <td class="value">{{ number_format($invoice->service_total, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td class="label">Grand Total:</td>
                            <td class="value">{{ number_format($invoice->grand_total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Paid Amount:</td>
                            <td class="value">{{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Due Amount:</td>
                            <td class="value">{{ number_format($invoice->due_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer">
            Thank you for your business!<br>
            Powered by Mamun Automobiles ERP
        </div>
    </div>
</body>
</html>
