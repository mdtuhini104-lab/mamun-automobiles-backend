<?php

namespace App\Events;

use App\Models\JobCard;
use App\Models\WorkshopBay;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkshopBayAllocated
{
    use Dispatchable, SerializesModels;

    public $jobCard;
    public $bay;

    public function __construct(JobCard $jobCard, ?WorkshopBay $bay)
    {
        $this->jobCard = $jobCard;
        $this->bay = $bay;
    }
}
