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

export function usePromptPolling(
    promptId: number | null | undefined
) {
    const [status, setStatus] = useState<PromptStatus>(
        promptId ? 'pending' : null
    );

    const [result, setResult] = useState<PromptResult | null>(null);

    useEffect(() => {
        if (!promptId) {
            setStatus(null);
            setResult(null);
            return;
        }

        let cancelled = false;

        const poll = async () => {
            try {
                const response = await axios.get<PromptResult>(
                    `/prompts/${promptId}/status`
                );

                if (cancelled) return;

                const data = response.data;

                console.log('POLL RESPONSE:', data);

                setStatus(data.status);

                if (
                    data.status === 'completed' ||
                    data.status === 'failed'
                ) {
                    setResult(data);
                }
            } catch (error) {
                console.error(
                    'Failed to fetch prompt status:',
                    error
                );
            }
        };

        // Gọi ngay lập tức
        poll();

        // Sau đó 1.5 giây gọi lại
        const interval = window.setInterval(
            poll,
            1500
        );

        return () => {
            cancelled = true;
            window.clearInterval(interval);
        };
    }, [promptId]);

    return {
        status,
        result,
    };
}
