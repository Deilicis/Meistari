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
    | 'job_auto_released';

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
