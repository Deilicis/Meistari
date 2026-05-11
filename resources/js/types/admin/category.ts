export interface AdminCategory {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    parent_id: number | null;
    is_system: boolean;
    services_count: number;
    job_requests_count: number;
    children_count: number;
    can_delete: boolean;
    children?: AdminCategory[];
    created_at: string;
}
