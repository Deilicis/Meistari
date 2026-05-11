export type SuggestionStatus = 'pending' | 'approved' | 'rejected' | 'merged';

export interface CategorySuggestion {
    id: number;
    name: string;
    parent_category: { id: number; name: string } | null;
    note: string | null;
    status: SuggestionStatus;
    status_label: string;
    created_at: string;
    suggested_by: { id: number; name: string };
}

export interface CategorySearchResult {
    approved: Array<{ id: number; name: string; slug: string; parent_id: number | null }>;
    pending: Array<{ id: number; name: string; suggested_by_name: string; created_at: string }>;
}

export type PickerSelection =
    | { type: 'category'; id: number; name: string }
    | { type: 'suggestion'; id: number; name: string };
