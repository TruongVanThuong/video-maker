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
    input_content?: string | null;
    analyzed_structure: AnalyzedStructure | null;
    final_prompt: string | null;
    target_platform?: string | null;
    error_message?: string | null;
    refinement_count?: number;
    is_manually_edited?: boolean;
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
    { key: 'subject', label: 'Subject' },
    { key: 'action', label: 'Action' },
    { key: 'environment', label: 'Environment' },
    { key: 'camera_movement', label: 'Camera movement' },
    { key: 'lighting_and_atmosphere', label: 'Lighting & atmosphere' },
    { key: 'final_video_prompt', label: 'Final prompt' },
] as const;

export const MAX_REFINEMENTS = 5;