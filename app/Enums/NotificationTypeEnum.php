<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case NEW_APPLICATION           = 'new_application';
    case APPLICATION_ACCEPTED      = 'application_accepted';
    case APPLICATION_SHORTLISTED   = 'application_shortlisted';
    case APPLICATION_REJECTED      = 'application_rejected';
    case NEW_MESSAGE          = 'new_message';
    case JOB_COMPLETED        = 'job_completed';
    case NEW_REVIEW           = 'new_review';
    case JOB_PAID             = 'job_paid';
    case JOB_MARKED_COMPLETE  = 'job_marked_complete';
    case JOB_CONFIRMED        = 'job_confirmed';
    case JOB_DISPUTED         = 'job_disputed';
    case JOB_CANCELLED        = 'job_cancelled';
    case JOB_AUTO_RELEASED    = 'job_auto_released';
    case PROPOSAL_RECEIVED    = 'proposal_received';
    case PROPOSAL_ACCEPTED    = 'proposal_accepted';
    case PROPOSAL_REJECTED    = 'proposal_rejected';
    case PROPOSAL_WITHDRAWN          = 'proposal_withdrawn';
    case NEW_CATEGORY_SUGGESTION     = 'new_category_suggestion';
}
