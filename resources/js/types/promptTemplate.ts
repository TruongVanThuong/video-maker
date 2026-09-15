export interface PromptTemplate {
    id: number;
    name: string;
    category: string;
    system_instruction: string;
    is_active: boolean;
    content_prompts_count?: number;
    created_at?: string;
    updated_at?: string;
}