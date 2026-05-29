<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'company_name',
        'subdomain',
        'domain',
        'status',
    ];
}
