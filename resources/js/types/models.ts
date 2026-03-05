export type JobStatus = 'active' | 'assigned' | 'completed' | 'cancelled';

export interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    parent_id: number | null;
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
}
