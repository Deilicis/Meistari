export type JobStatus = 'open' | 'accepted' | 'in_progress' | 'awaiting_confirmation' | 'completed' | 'disputed' | 'cancelled';

export interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    parent_id: number | null;
    children?: Category[];
}

export interface JobRequest {
    id: number;
    user_id: number;
    category_id: number;
    category?: Category;
    title: string;
    description: string;
    budget: number | null;
    deadline: string | null;
    location: string[];
    images: string[];
    status: JobStatus;
    applications_count?: number;
    created_at: string;
    updated_at: string;
    pending_category_suggestion?: { id: number; name: string; status: string } | null;
}

export interface Service {
    id: number;
    user_id: number;
    category_id: number;
    category?: Category;
    title: string;
    slug: string;
    description: string;
    price: number | null;
    price_type: 'hourly' | 'fixed' | 'negotiable';
    location: string[];
    is_active: boolean;
    created_at: string;
    updated_at: string;
    applications_count?: number;
    pending_category_suggestion?: { id: number; name: string; status: string } | null;
}

export interface Profile {
    id: number;
    user_id: number;
    type: 'individual' | 'company';
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    city: string;
    phone: string | null;
    bio: string | null;
    avatar: string | null;
    is_verified: boolean;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
    active_role: 'master' | 'seeker' | null;
    is_master: boolean;
    is_seeker: boolean;
    has_both_roles: boolean;
}

export type ServiceApplicationStatus = 'pending' | 'accepted' | 'rejected' | 'completed' | 'cancelled';

export interface ServiceApplication {
    id: number;
    service_id: number;
    user_id: number;
    message: string;
    budget_offer: number | null;
    status: ServiceApplicationStatus;
    created_at: string;
    updated_at: string;
}

export interface ServiceApplicationWithService {
    id: number;
    message: string;
    budget_offer: number | null;
    status: ServiceApplicationStatus;
    created_at: string;
    service: {
        id: number;
        title: string;
        slug: string;
        description: string;
        price: number | null;
        price_type: 'hourly' | 'fixed' | 'negotiable';
        location: string[];
        is_active: boolean;
        category: { id: number; name: string } | null;
        user: {
            id: number;
            name: string;
            profile: ServiceMasterProfile | null;
        };
    };
}

export interface ServiceMasterProfile {
    type: 'individual' | 'company';
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    city: string;
    bio: string | null;
    avatar: string | null;
    is_verified: boolean;
    experiences: { title: string; company: string; description?: string }[];
    portfolio_images: string[];
}

export type ApplicationStatus = 'pending' | 'shortlisted' | 'accepted' | 'rejected' | 'completed' | 'cancelled';

export interface SeekerProfile {
    type: 'individual' | 'company';
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    city: string;
    avatar: string | null;
}

export interface JobRequestWithSeeker extends JobRequest {
    applications_count: number;
    user: {
        id: number;
        name: string;
        profile: SeekerProfile | null;
    };
}

export interface ApplicationWithJobRequest {
    id: number;
    cover_letter: string | null;
    price_offer: number | null;
    status: ApplicationStatus;
    created_at: string;
    job_request: {
        id: number;
        title: string;
        description: string;
        budget: number | null;
        deadline: string | null;
        location: string[];
        status: JobStatus;
        category: { id: number; name: string } | null;
        user: {
            id: number;
            name: string;
            profile: SeekerProfile | null;
        } | null;
    } | null;
}

export interface JobApplication {
    id: number;
    cover_letter: string | null;
    price_offer: number | null;
    status: ApplicationStatus;
    created_at: string;
    user: {
        id: number;
        name: string;
        profile: SeekerProfile | null;
    };
}

export interface ReviewData {
    id: number;
    rating: number;
    comment: string | null;
    reviewer_id: number;
    reviewee_id: number;
    created_at: string;
}

export interface MasterPublicReview {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    reviewer: {
        id: number;
        name: string;
        profile: SeekerProfile | null;
    };
}

export interface ServiceWithMaster extends Service {
    user: {
        id: number;
        name: string;
        profile: ServiceMasterProfile | null;
    };
}
