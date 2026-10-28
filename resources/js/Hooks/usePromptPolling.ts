import { useCallback, useEffect, useRef, useState } from 'react';
import axios from 'axios';
import { PromptResult, PromptStatus } from '@/types/prompt';

const POLL_INTERVAL_MS = 1500;
const MAX_POLL_DURATION_MS = 5 * 60 * 1000;

export function usePromptPolling(initialPromptId: number | null | undefined) {
    const [activeId, setActiveId] = useState<number | null>(initialPromptId ?? null);
    const [status, setStatus] = useState<PromptStatus>(initialPromptId ? 'pending' : null);
    const [result, setResult] = useState<PromptResult | null>(null);
    const [pollNonce, setPollNonce] = useState(0);

    const statusRef = useRef(status);
    statusRef.current = status;

    useEffect(() => {
        if (initialPromptId) {
            setActiveId(initialPromptId);
            setStatus('pending');
            setResult(null);
        }
    }, [initialPromptId]);

    useEffect(() => {
        if (!activeId) return;

        const controller = new AbortController();
        let timeoutId: number | undefined;
        const startedAt = Date.now();

        const scheduleNext = () => {
            if (statusRef.current === 'completed' || statusRef.current === 'failed') return;

            if (Date.now() - startedAt > MAX_POLL_DURATION_MS) {
                setStatus('failed');
                setResult((prev) =>
                    prev ? { ...prev, status: 'failed', error_message: 'Hết thời gian chờ xử lý.' } : null
                );
                return;
            }

            timeoutId = window.setTimeout(poll, POLL_INTERVAL_MS);
        };

        const poll = async () => {
            try {
                const response = await axios.get<PromptResult>(`/prompts/${activeId}/status`, {
                    signal: controller.signal,
                });
                setStatus(response.data.status);
                setResult(response.data);
                scheduleNext();
            } catch (error) {
                if (axios.isCancel(error)) return;
                console.error('Failed to fetch prompt status:', error);
                setStatus('failed');
                setResult((prev) =>
                    prev ? { ...prev, status: 'failed', error_message: 'Không thể kết nối tới server.' } : null
                );
            }
        };

        poll();

        return () => {
            controller.abort();
            if (timeoutId) window.clearTimeout(timeoutId);
        };
        // pollNonce cho phép ép effect chạy lại (ví dụ sau khi trigger refine)
        // dù activeId không đổi.
    }, [activeId, pollNonce]);

    const selectPromptResult = useCallback((selectedResult: PromptResult) => {
        setActiveId(selectedResult.id);
        setStatus(selectedResult.status);
        setResult(selectedResult);
    }, []);

    // Gọi hàm này ngay sau khi trigger refine thành công (status trả về 'processing')
    const markAsRefining = useCallback(() => {
        setStatus('processing');
        setPollNonce((n) => n + 1);
    }, []);

    const resetPolling = useCallback(() => {
        setActiveId(null);
        setStatus(null);
        setResult(null);
    }, []);

    return { activeId, status, result, selectPromptResult, resetPolling, markAsRefining };
}