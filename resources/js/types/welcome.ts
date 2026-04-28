export interface WelcomeServiceCard {
    id: number;
    title: string;
    description: string;
    price: number | null;
    price_type: 'hourly' | 'fixed' | 'negotiable';
    location: string[];
    category: { id: number; name: string } | null;
    user: {
        id: number;
        name: string;
        profile: {
            first_name: string | null;
            last_name: string | null;
            company_name: string | null;
            type: string;
            avatar: string | null;
            city: string;
            is_verified: boolean;
        } | null;
    };
}

export interface WelcomeJobRequestCard {
    id: number;
    title: string;
    description: string;
    budget: number | null;
    deadline: string | null;
    location: string[];
    applications_count: number;
    category: { id: number; name: string } | null;
    user: {
        id: number;
        name: string;
        profile: {
            first_name: string | null;
            last_name: string | null;
            company_name: string | null;
            type: string;
            avatar: string | null;
            city: string;
        } | null;
    };
}

export interface PopularCategory {
    id: number;
    name: string;
    services_count: number;
}

export interface FeaturedMasterProfile {
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    type: string | null;
    avatar: string | null;
    city: string | null;
    bio: string | null;
    is_verified: boolean;
}

export interface FeaturedMaster {
    id: number;
    name: string;
    reviews_received_count: number;
    reviews_received_avg_rating: number | null;
    profile: FeaturedMasterProfile | null;
}

export interface WelcomeStats {
    masters: number;
    completed_jobs: number;
    categories: number;
}

export type WelcomeFilters = {
    search?: string;
    category_id?: string;
    tab?: string;
};
