<?php

namespace App\Entity\Enum;

enum DreamStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
