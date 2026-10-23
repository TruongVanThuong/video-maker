import { useCallback, useEffect, useRef, useState } from 'react';
import axios from 'axios';
import { PromptResult, PromptStatus } from '@/types/prompt';

const POLL_INTERVAL_MS = 1500;
const MAX_POLL_DURATION_MS = 5 * 60 * 1000;

// Job phân tích có tries = 3 nên sau khi ghi nhận lỗi, queue vẫn có thể
// chạy lại job. Poll thêm một khoảng ngắn để nhận kết quả của lần chạy đó.
const FAILURE_GRACE_MS = 45 * 1000;

export function usePromptPolling(initialPromptId: number | null | undefined) {
    const [activeId, setActiveId] = useState<number | null>(initialPromptId ?? null);
    const [status, setStatus] = useState<PromptStatus>(initialPromptId ? 'pending' : null);
    const [result, setResult] = useState<PromptResult | null>(null);
    const [pollNonce, setPollNonce] = useState(0);

    const statusRef = useRef(status);
    statusRef.current = status;

    const activeIdRef = useRef(activeId);
    activeIdRef.current = activeId;

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
        let failedAt: number | null = null;

        const scheduleNext = () => {
            const currentStatus = statusRef.current;

            if (currentStatus === 'completed') return;

            if (currentStatus === 'failed') {
                failedAt ??= Date.now();

                if (Date.now() - failedAt > FAILURE_GRACE_MS) return;
            }

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
        // pollNonce cho phép ép effect chạy lại (ví dụ sau khi trigger refine
        // hoặc retry) dù activeId không đổi.
    }, [activeId, pollNonce]);

    const selectPromptResult = useCallback((selectedResult: PromptResult) => {
        setActiveId(selectedResult.id);
        setStatus(selectedResult.status);
        setResult(selectedResult);
    }, []);

    /**
     * Đánh dấu prompt đang xử lý và khởi động lại polling.
     *
     * Dùng cho refine và retry: polling phải theo dõi đúng bản ghi đang chạy.
     * Nếu backend trả về id khác (mỗi lần phân tích là một bản ghi riêng)
     * thì id đang theo dõi được cập nhật theo id mới.
     */
    const markAsProcessing = useCallback((promptId?: number | null) => {
        if (promptId && promptId !== activeIdRef.current) {
            activeIdRef.current = promptId;
            setActiveId(promptId);
            setResult(null);
        }

        setStatus('processing');
        setPollNonce((n) => n + 1);
    }, []);

    const resetPolling = useCallback(() => {
        setActiveId(null);
        setStatus(null);
        setResult(null);
    }, []);

    return { activeId, status, result, selectPromptResult, resetPolling, markAsProcessing };
}
