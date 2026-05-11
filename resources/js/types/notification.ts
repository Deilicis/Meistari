export type NotificationType =
    | 'new_application'
    | 'application_accepted'
    | 'application_rejected'
    | 'new_message'
    | 'job_completed'
    | 'new_review'
    | 'job_paid'
    | 'job_marked_complete'
    | 'job_confirmed'
    | 'job_disputed'
    | 'job_cancelled'
    | 'job_auto_released'
    | 'application_shortlisted'
    | 'proposal_received'
    | 'proposal_accepted'
    | 'proposal_rejected'
    | 'proposal_withdrawn'
    | 'new_category_suggestion'
    | 'category_suggestion_approved'
    | 'category_suggestion_rejected'
    | 'category_suggestion_merged';

export interface Notification {
    id: number;
    type: NotificationType;
    title: string;
    body: string | null;
    action_url: string | null;
    metadata: Record<string, unknown> | null;
    read_at: string | null;
    is_read: boolean;
    created_at: string;
}
