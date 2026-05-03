<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function isEmployerDecision(): bool
    {
        return in_array($this, [self::Accepted, self::Rejected], true);
    }
}
