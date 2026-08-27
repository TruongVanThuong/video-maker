import { useState, useEffect } from 'react';
import axios from 'axios';

export type PromptStatus = 'pending' | 'processing' | 'completed' | 'failed' | null;

export interface PromptResult {
    id: number;
    status: PromptStatus;
    final_prompt: string | null;
    analyzed_structure: Record<string, unknown> | null;
    error_message?: string;
}

export function usePromptPolling(initialPromptId: number | null) {
    const [activePromptId, setActivePromptId] = useState<number | null>(initialPromptId);
    const [status, setStatus] = useState<PromptStatus>(initialPromptId ? 'processing' : null);
    const [result, setResult] = useState<PromptResult | null>(null);

    // Cập nhật ID khi submit form thành công từ Flash message
    const startPolling = (id: number) => {
        setActivePromptId(id);
        setStatus('processing');
        setResult(null);
    };

    useEffect(() => {
        if (!activePromptId || status === 'completed' || status === 'failed') return;

        const interval = window.setInterval(async () => {
            try {
                const response = await axios.get<PromptResult>(`/prompts/${activePromptId}/status`);
                const currentStatus = response.data.status;

                setStatus(currentStatus);

                if (currentStatus === 'completed') {
                    setResult(response.data);
                    window.clearInterval(interval);
                } else if (currentStatus === 'failed') {
                    setResult(response.data);
                    window.clearInterval(interval);
                }
            } catch (error) {
                console.error('Failed to fetch prompt status:', error);
            }
        }, 1500);

        return () => window.clearInterval(interval);
    }, [activePromptId, status]);

    return { status, result, startPolling };
}