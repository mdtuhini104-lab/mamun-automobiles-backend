<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $res = \App\Models\Invoice::select(
            \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
            \Illuminate\Support\Facades\DB::raw('SUM(grand_total) as total')
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->get()
        ->toArray();
    print_r($res);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
