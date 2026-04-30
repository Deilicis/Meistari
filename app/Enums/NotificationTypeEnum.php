<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case NEW_APPLICATION      = 'new_application';
    case APPLICATION_ACCEPTED = 'application_accepted';
    case APPLICATION_REJECTED = 'application_rejected';
    case NEW_MESSAGE          = 'new_message';
    case JOB_COMPLETED        = 'job_completed';
    case NEW_REVIEW           = 'new_review';
}
