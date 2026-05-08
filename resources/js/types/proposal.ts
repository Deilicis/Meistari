export type ProposalStatus = 'pending' | 'accepted' | 'countered' | 'rejected' | 'withdrawn';

export interface PriceProposal {
    id: number;
    amount: string;
    price_type: 'fixed';
    note: string | null;
    status: ProposalStatus;
    status_label: string;
    proposed_by: { id: number; name: string };
    responded_by: { id: number; name: string } | null;
    responded_at: string | null;
    created_at: string;
    is_pending: boolean;
}

export interface ProposalState {
    application_id: number;
    pending: PriceProposal | null;
    history: PriceProposal[];
    can_submit_fresh: boolean;
    can_counter: boolean;
    can_accept: boolean;
    can_reject: boolean;
    can_withdraw: boolean;
}
