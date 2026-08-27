import React, { FormEvent, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { usePromptPolling, PromptStatus, PromptResult } from '@/Hooks/usePromptPolling';

// --- TYPES ---
interface PromptTemplate {
    id: number;
    name: string;
    category: string;
}

interface RecentPrompt {
    id: number;
    input_content: string;
    status: PromptStatus;
    final_prompt: string | null;
    created_at: string;
}

interface PageProps {
    templates: PromptTemplate[];
    recentPrompts: RecentPrompt[];
    flash?: { prompt_id?: number };
}

interface FormData {
    input_content: string;
    prompt_template_id: number | '';
}

export default function Index() {
    const { templates, recentPrompts, flash } = usePage<PageProps>().props;
    const { status, result, startPolling } = usePromptPolling(null);

    const { data, setData, post, processing, errors, reset } = useForm<FormData>({
        input_content: '',
        prompt_template_id: '',
    });

    // Lắng nghe flash.prompt_id để kích hoạt polling
    useEffect(() => {
        if (flash?.prompt_id) {
            startPolling(flash.prompt_id);
        }
    }, [flash?.prompt_id]);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/prompts', {
            preserveScroll: true,
            onSuccess: () => reset('input_content', 'prompt_template_id'),
        });
    };

    const isProcessing = processing || status === 'processing' || status === 'pending';

    return (
        <div className="max-w-3xl mx-auto p-6 space-y-8">
            <h2 className="text-2xl font-bold">Video Prompt Generator</h2>

            {/* FORM */}
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="prompt_template_id" className="block text-sm font-medium mb-1">
                        Style Template
                    </label>
                    <select
                        id="prompt_template_id"
                        value={data.prompt_template_id}
                        onChange={(e) => setData('prompt_template_id', e.target.value ? Number(e.target.value) : '')}
                        disabled={isProcessing}
                        className="w-full p-2 border rounded-md disabled:bg-gray-100"
                    >
                        <option value="">-- Mặc định (Tự động) --</option>
                        {templates.map((tpl) => (
                            <option key={tpl.id} value={tpl.id}>{tpl.name}</option>
                        ))}
                    </select>
                    {errors.prompt_template_id && <p className="text-red-500 text-sm mt-1">{errors.prompt_template_id}</p>}
                </div>

                <div>
                    <label htmlFor="input_content" className="block text-sm font-medium mb-1">
                        Nội dung cần phân tích
                    </label>
                    <textarea
                        id="input_content"
                        rows={5}
                        value={data.input_content}
                        onChange={(e) => setData('input_content', e.target.value)}
                        disabled={isProcessing}
                        placeholder="Nhập kịch bản hoặc ý tưởng đoạn phim..."
                        className="w-full p-2 border rounded-md disabled:bg-gray-100"
                    />
                    {errors.input_content && <p className="text-red-500 text-sm mt-1">{errors.input_content}</p>}
                </div>

                <button
                    type="submit"
                    disabled={isProcessing}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {isProcessing ? 'Đang phân tích...' : 'Phân tích & Tạo Prompt'}
                </button>
            </form>

            {/* TRẠNG THÁI XỬ LÝ */}
            <StatusDisplay status={status} result={result} />

            {/* DANH SÁCH GẦN ĐÂY */}
            <RecentPromptsList prompts={recentPrompts} />
        </div>
    );
}

// --- SUB COMPONENTS (Giúp Index gọn gàng hơn rất nhiều) ---

function StatusDisplay({ status, result }: { status: PromptStatus; result: PromptResult | null }) {
    if (status === 'processing' || status === 'pending') {
        return <div className="p-4 bg-amber-50 text-amber-800 rounded-md">⏳ Đang xử lý qua AI, vui lòng chờ...</div>;
    }

    if (status === 'failed') {
        return <div className="p-4 bg-red-50 text-red-800 rounded-md">❌ Lỗi: Không thể tạo prompt. Vui lòng thử lại.</div>;
    }

    if (status === 'completed' && result) {
        return (
            <div className="p-4 bg-gray-50 border rounded-md space-y-4">
                <h3 className="font-semibold text-lg">Prompt Video Hoàn Chỉnh</h3>
                <textarea
                    readOnly
                    rows={4}
                    value={result.final_prompt || ''}
                    className="w-full p-2 font-mono font-bold bg-white border rounded-md"
                />

                <h4 className="font-semibold">Bối cảnh chi tiết (JSON DTO)</h4>
                <pre className="p-3 bg-gray-900 text-green-400 rounded-md overflow-x-auto text-sm">
                    {JSON.stringify(result.analyzed_structure, null, 2)}
                </pre>
            </div>
        );
    }

    return null;
}

function RecentPromptsList({ prompts }: { prompts: RecentPrompt[] }) {
    if (prompts.length === 0) return null;

    return (
        <div className="space-y-3 pt-6 border-t">
            <h3 className="font-bold text-lg">Prompt gần đây</h3>
            {prompts.map((p) => (
                <div key={p.id} className="p-3 border rounded-md space-y-1">
                    <p className="font-medium line-clamp-1">{p.input_content}</p>
                    <span className="text-xs px-2 py-0.5 bg-gray-200 rounded-full capitalize">{p.status}</span>
                </div>
            ))}
        </div>
    );
}