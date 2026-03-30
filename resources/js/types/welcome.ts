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
}

export type WelcomeFilters = {
    search?: string;
    category_id?: string;
    tab?: string;
};
