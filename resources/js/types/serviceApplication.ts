export type ServiceApplicationStatus = 'pending' | 'accepted' | 'rejected' | 'completed' | 'cancelled';

export interface MasterServiceApplication {
    id: number;
    status: ServiceApplicationStatus;
    message: string | null;
    budget_offer: string | null;
    created_at: string;
    service: {
        id: number;
        title: string;
    };
    applicant: {
        id: number;
        name: string;
        avatar_url: string | null;
    };
}
