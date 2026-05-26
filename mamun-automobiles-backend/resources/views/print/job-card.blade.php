<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'sans-serif'; font-size: 14px; color: #333; }
        .header { width: 100%; text-align: center; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <p>Mamun Automobiles Workshop Management</p>
    </div>

    <table>
        <tr>
            <th width="30%">Reference ID</th>
            <td>{{ $id }}</td>
        </tr>
        <tr>
            <th>Date</th>
            <td>{{ date('Y-m-d') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>Generated</td>
        </tr>
    </table>

    <p style="text-align: center; margin-top: 50px; color: #666;">
        This document is system generated and does not require a signature.
    </p>
</body>
</html>
