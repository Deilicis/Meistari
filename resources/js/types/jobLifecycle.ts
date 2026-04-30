export type JobStatus =
    | 'open'
    | 'accepted'
    | 'in_progress'
    | 'awaiting_confirmation'
    | 'completed'
    | 'disputed'
    | 'cancelled';

export type JobAllowedAction =
    | 'accept_application'
    | 'pay'
    | 'mark_complete'
    | 'confirm'
    | 'dispute'
    | 'cancel';

export interface EscrowInfo {
    status: 'held' | 'released' | 'refunded';
    amount: string;
    held_at: string | null;
    auto_release_at: string | null;
}

export interface JobLifecycle {
    id: number;
    title: string;
    description: string;
    status: JobStatus;
    status_label: string;
    agreed_price: string | null;
    price_type: 'fixed' | 'hourly' | null;
    master_completed_at: string | null;
    completed_at: string | null;
    created_at: string;
    client: { id: number; name: string };
    master: { id: number; name: string } | null;
    escrow: EscrowInfo | null;
    allowed_actions: JobAllowedAction[];
}
