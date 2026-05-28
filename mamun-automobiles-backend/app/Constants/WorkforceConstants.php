<?php

namespace App\Constants;

class WorkforceConstants
{
    // Assignment Types
    public const ASSIGN_LEAD = 'lead_technician';
    public const ASSIGN_ASSISTANT = 'assistant_technician';
    public const ASSIGN_HELPER = 'helper';

    // Employment Statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_RESIGNED = 'resigned';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_ON_LEAVE = 'on_leave';

    // Skill Levels
    public const SKILL_JUNIOR = 'junior';
    public const SKILL_MID = 'mid';
    public const SKILL_SENIOR = 'senior';
    public const SKILL_EXPERT = 'expert';

    // Availability Engine Statuses
    public const AVAIL_AVAILABLE = 'available';
    public const AVAIL_BUSY = 'busy';
    public const AVAIL_ASSIGNED = 'assigned';
    public const AVAIL_LEAVE = 'on_leave';
    public const AVAIL_OFFLINE = 'offline';
}
