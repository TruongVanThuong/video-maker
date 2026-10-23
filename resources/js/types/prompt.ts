export type PromptStatus = 'pending' | 'processing' | 'completed' | 'failed' | null;

export interface AnalyzedStructure {
    subject?: string | null;
    action?: string | null;
    environment?: string | null;
    camera_movement?: string | null;
    lighting_and_atmosphere?: string | null;
    [key: string]: unknown;
}

export interface PromptResult {
    id: number;
    status: Exclude<PromptStatus, null>;
    conversion_status?: 'unconverted' | 'converted' | string | null;
    project_id?: number | null;
    input_content?: string | null;
    analyzed_structure: AnalyzedStructure | null;
    final_prompt: string | null;
    target_platform?: string | null;
    error_message?: string | null;
    refinement_count?: number;
    is_manually_edited?: boolean;
    prompt_template_id?: number | null;
}

export interface RecentPrompt extends PromptResult {
    created_at: string;
}

export interface PromptFormData {
    input_content: string;
    prompt_template_id: number | '';
    target_platform: string;
}

export const REFINABLE_FIELDS = [
    { key: 'subject', label: 'Chủ thể' },
    { key: 'action', label: 'Hành động' },
    { key: 'environment', label: 'Bối cảnh' },
    { key: 'camera_movement', label: 'Chuyển động camera' },
    { key: 'lighting_and_atmosphere', label: 'Ánh sáng & không khí' },
    { key: 'final_video_prompt', label: 'Prompt cuối' },
] as const;

export const MAX_REFINEMENTS = 5;

export interface PromptPaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PromptPaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PromptPaginationLink[];
}

export interface PromptPagination<T> extends PromptPaginationMeta {
    data: T[];
}