<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case PENDING = 'pending';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';
}
