<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiMonthlyAggregate extends Model
{
    use HasFactory;

    protected $table = 'kpi_monthly_aggregates';

    protected $fillable = [
        'year',
        'month',
        'revenue',
        'quotation_conversion_ratio',
        'bay_utilization_ratio',
        'technician_efficiency_score',
        'inventory_turnover',
        'comeback_rate',
        'customer_retention_rate',
    ];

    protected $casts = [
        'revenue' => 'float',
        'quotation_conversion_ratio' => 'float',
        'bay_utilization_ratio' => 'float',
        'technician_efficiency_score' => 'float',
        'inventory_turnover' => 'float',
        'comeback_rate' => 'float',
        'customer_retention_rate' => 'float',
    ];
}
