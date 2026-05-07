export type ServiceApplicationStatus = 'pending' | 'shortlisted' | 'accepted' | 'rejected' | 'completed' | 'cancelled';
export type JobApplicationStatus = ServiceApplicationStatus;

export interface SeekerJobApplication {
    id: number;
    status: JobApplicationStatus;
    cover_letter: string | null;
    price_offer: string | null;
    created_at: string;
    job_request: {
        id: number;
        title: string;
    } | null;
    applicant: {
        id: number;
        name: string;
        avatar_url: string | null;
    } | null;
}

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
