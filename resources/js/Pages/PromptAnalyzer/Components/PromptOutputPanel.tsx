import { useState } from 'react';
import {
    Film,
    Clapperboard,
    AlertCircle,
    Clock,
    Wand2,
    Check,
    RotateCcw,
    MonitorPlay,
    RefreshCcw,
} from 'lucide-react';
import ContextCard from './ContextCard';
import axios from 'axios';
import { PromptResult, PromptStatus, REFINABLE_FIELDS, MAX_REFINEMENTS } from '@/types/prompt';


interface PromptOutputPanelProps {
    status: PromptStatus;
    result: PromptResult | null;
    activeId: number | null;

    onRetry: () => void;
    onSendPipeline: () => void;
    onRefineStart: () => void;
    onResultUpdated: (result: PromptResult) => void;
}

type OutputTab = 'structured' | 'json';

export default function PromptOutputPanel({
    status,
    result,
    activeId,
    onRetry,
    onSendPipeline,
    onRefineStart,
    onResultUpdated
}: PromptOutputPanelProps) {
    const [copied, setCopied] = useState(false);
    const [pipelineSent, setPipelineSent] = useState(false);
    const [activeTab, setActiveTab] = useState<OutputTab>('structured');

    const copyToClipboard = async (text: string) => {
        if (!text) {
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);

            window.setTimeout(() => {
                setCopied(false);
            }, 2500);
        } catch (error) {
            console.error('Failed to copy prompt:', error);
        }
    };

    const handleSendPipeline = () => {
        onSendPipeline();

        setPipelineSent(true);

        window.setTimeout(() => {
            setPipelineSent(false);
        }, 3000);
    };

    const renderStatusBadge = () => {
        if (!status) {
            return null;
        }

        const isCompleted = status === 'completed';
        const isProcessing =
            status === 'processing' || status === 'pending';
        const isFailed = status === 'failed';

        return (
            <span
                className={`text-xs font-semibold px-2.5 py-1 rounded-full border flex items-center gap-1.5 uppercase tracking-wider ${
                    isCompleted
                        ? 'bg-emerald-950/80 text-emerald-300 border-emerald-700/50'
                        : isProcessing
                        ? 'bg-amber-950/80 text-amber-300 border-amber-700/50 animate-pulse'
                        : isFailed
                        ? 'bg-rose-950/80 text-rose-300 border-rose-700/50'
                        : 'bg-gray-800 text-gray-300 border-gray-700'
                }`}
            >
                <span
                    className={`w-2 h-2 rounded-full ${
                        isCompleted
                            ? 'bg-emerald-400'
                            : isFailed
                            ? 'bg-rose-400'
                            : 'bg-amber-400 animate-ping'
                    }`}
                />

                {status}
            </span>
        );
    };

    const [savingField, setSavingField] = useState<string | null>(null);
    const [refineOpen, setRefineOpen] = useState(false);
    const [refineInstruction, setRefineInstruction] = useState('');
    const [refineFields, setRefineFields] = useState<string[]>(['all']);
    const [refineError, setRefineError] = useState<string | null>(null);
    const [refineSubmitting, setRefineSubmitting] = useState(false);

    const refinementCount = result?.refinement_count ?? 0;
    const refineDisabled = refinementCount >= MAX_REFINEMENTS;

    const saveField = async (fieldKey: string, newValue: string) => {
        if (!result) return;
        setSavingField(fieldKey);

        try {
            const isFinalPrompt = fieldKey === 'final_video_prompt';
            const response = await axios.patch<PromptResult>(`/prompts/${result.id}`, {
                analyzed_structure: isFinalPrompt ? undefined : { [fieldKey]: newValue },
                final_prompt: isFinalPrompt ? newValue : undefined,
            });
            onResultUpdated(response.data);
        } catch (error) {
            console.error('Failed to save field:', error);
            // TODO: hiển thị toast lỗi thay vì chỉ log
        } finally {
            setSavingField(null);
        }
    };

    const toggleRefineField = (key: string) => {
        setRefineFields((prev) => {
            if (key === 'all') return ['all'];
            const withoutAll = prev.filter((f) => f !== 'all');
            return withoutAll.includes(key)
                ? withoutAll.filter((f) => f !== key)
                : [...withoutAll, key];
        });
    };

    const submitRefine = async () => {
        if (!result || !refineInstruction.trim()) return;

        setRefineSubmitting(true);
        setRefineError(null);

        try {
            await axios.post(`/prompts/${result.id}/refine`, {
                instruction: refineInstruction.trim(),
                fields: refineFields,
            });

            onRefineStart(); // báo hook chuyển sang 'processing' + restart polling
            setRefineOpen(false);
            setRefineInstruction('');
            setRefineFields(['all']);
        } catch (error) {
            if (axios.isAxiosError(error)) {
                setRefineError(
                    error.response?.data?.message || 'Không thể gửi yêu cầu tinh chỉnh.'
                );
            } else {
                setRefineError('Đã xảy ra lỗi không xác định.');
            }
        } finally {
            setRefineSubmitting(false);
        }
    };

    return (
        <div className="lg:col-span-7 bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-6 shadow-2xl min-h-[560px] flex flex-col justify-between">
            <div>
                {/* HEADER */}
                <div className="flex items-center justify-between border-b border-gray-800 pb-4 mb-6">
                    <h2 className="text-lg font-bold text-white flex items-center gap-2">
                        <Film className="w-5 h-5 text-emerald-400" />
                        Prompt Studio Output
                    </h2>

                    {renderStatusBadge()}
                </div>

                {/* IDLE */}
                {!status && (
                    <div className="py-16 text-center space-y-4 max-w-md mx-auto">
                        <div className="w-16 h-16 mx-auto rounded-2xl bg-gray-800/80 border border-gray-700 flex items-center justify-center text-gray-500 shadow-inner">
                            <Clapperboard className="w-8 h-8 text-indigo-400" />
                        </div>

                        <div>
                            <h3 className="text-base font-bold text-gray-200">
                                No Prompt Generated Yet
                            </h3>

                            <p className="text-xs text-gray-400 mt-1">
                                Select your style template, choose target
                                video engine, enter script content, and
                                click "Analyze & Generate".
                            </p>
                        </div>

                        <div className="pt-4 border-t border-gray-800/60 grid grid-cols-2 gap-3 text-left">
                            <div className="p-3 rounded-xl bg-gray-950/50 border border-gray-800/80">
                                <h4 className="text-xs font-semibold text-violet-300 mb-1 flex items-center gap-1">
                                    <Wand2 className="w-3.5 h-3.5" />
                                    Structured DTO
                                </h4>

                                <p className="text-[11px] text-gray-400">
                                    Extracts subject, action, lighting,
                                    camera angles automatically.
                                </p>
                            </div>

                            <div className="p-3 rounded-xl bg-gray-950/50 border border-gray-800/80">
                                <h4 className="text-xs font-semibold text-indigo-300 mb-1 flex items-center gap-1">
                                    <MonitorPlay className="w-3.5 h-3.5" />
                                    Engine Tailored
                                </h4>

                                <p className="text-[11px] text-gray-400">
                                    Optimized for Sora, Runway, Kling &
                                    Midjourney format.
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* PROCESSING */}
                {(status === 'processing' ||
                    status === 'pending') && (
                    <div className="py-12 px-6 text-center space-y-6 bg-gray-950/60 border border-gray-800 rounded-xl my-4">
                        <div className="relative w-20 h-20 mx-auto">
                            <div className="absolute inset-0 rounded-full border-4 border-violet-500/20 border-t-violet-500 animate-spin" />

                            <div
                                className="absolute inset-2 rounded-full border-4 border-indigo-500/20 border-b-indigo-500 animate-spin"
                                style={{
                                    animationDirection: 'reverse',
                                    animationDuration: '1.5s',
                                }}
                            />

                            <div className="absolute inset-0 flex items-center justify-center text-violet-400">
                                <Wand2 className="w-8 h-8 animate-pulse" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <h3 className="text-base font-bold text-white">
                                Analyzing Context with Gemini AI...
                            </h3>

                            <p className="text-xs text-gray-400">
                                Extracting camera dynamics, atmospheric
                                cues & subject movement
                            </p>
                        </div>

                        <div className="max-w-xs mx-auto space-y-2 text-left text-xs bg-gray-900 p-3.5 rounded-xl border border-gray-800">
                            <div className="flex items-center space-x-2 text-emerald-400">
                                <Check className="w-3.5 h-3.5" />
                                <span>
                                    Receiving input script parameters
                                </span>
                            </div>

                            <div className="flex items-center space-x-2 text-amber-400 animate-pulse">
                                <Clock className="w-3.5 h-3.5" />
                                <span>
                                    Running structured JSON decomposition
                                </span>
                            </div>

                            <div className="flex items-center space-x-2 text-gray-500">
                                <span className="w-3.5 h-3.5 rounded-full border border-gray-600 block" />
                                <span>
                                    Formatting engine prompt payload
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* FAILED */}
                {status === 'failed' && (
                    <div className="py-8 px-6 bg-rose-950/30 border border-rose-800/60 rounded-xl my-4 space-y-4">
                        <div className="flex items-start space-x-3 text-rose-400">
                            <AlertCircle className="w-6 h-6 shrink-0 mt-0.5" />

                            <div>
                                <h3 className="font-bold text-sm text-rose-300">
                                    Prompt Analysis Failed
                                </h3>

                                <p className="text-xs text-rose-300/80 mt-1">
                                    {result?.error_message ||
                                        'An unexpected error occurred while contacting the AI API.'}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={onRetry}
                            className="px-4 py-2 bg-rose-800 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5"
                        >
                            <RotateCcw className="w-3.5 h-3.5" />
                            <span>Retry Analysis</span>
                        </button>
                    </div>
                )}

                {/* COMPLETED */}
                {status === 'completed' && result && (
                    <div className="space-y-6">
                        {/* FINAL PROMPT — thêm khả năng edit trực tiếp */}
                        <div className="bg-gray-950 border border-violet-900/50 rounded-xl p-4 space-y-3">
                            <div className="flex items-center justify-between border-b border-gray-800/80 pb-2">
                                <span className="text-xs font-extrabold uppercase tracking-wider text-violet-400">
                                    Final English Video Prompt
                                    {result.is_manually_edited && (
                                        <span className="ml-2 text-[10px] normal-case font-semibold text-amber-400">
                                            (đã sửa tay)
                                        </span>
                                    )}
                                </span>
                                {/* nút copy / send pipeline giữ nguyên như cũ */}
                            </div>

                            <ContextCard
                                title="Final prompt"
                                value={result.final_prompt}
                                icon="🎬"
                                color="text-emerald-300"
                                fullWidth
                                onSave={(val) => saveField('final_video_prompt', val)}
                                saving={savingField === 'final_video_prompt'}
                            />
                        </div>

                        {/* STRUCTURED CONTEXT — mỗi card giờ có nút sửa */}
                        {result.analyzed_structure && (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <ContextCard
                                    title="Subject" icon="👤" color="text-amber-300"
                                    value={result.analyzed_structure.subject}
                                    onSave={(val) => saveField('subject', val)}
                                    saving={savingField === 'subject'}
                                />
                                <ContextCard
                                    title="Action" icon="🏃" color="text-blue-300"
                                    value={result.analyzed_structure.action}
                                    onSave={(val) => saveField('action', val)}
                                    saving={savingField === 'action'}
                                />
                                <ContextCard
                                    title="Environment" icon="🏞️" color="text-emerald-300"
                                    value={result.analyzed_structure.environment}
                                    onSave={(val) => saveField('environment', val)}
                                    saving={savingField === 'environment'}
                                />
                                <ContextCard
                                    title="Camera Movement" icon="🎥" color="text-purple-300"
                                    value={result.analyzed_structure.camera_movement}
                                    onSave={(val) => saveField('camera_movement', val)}
                                    saving={savingField === 'camera_movement'}
                                />
                                <ContextCard
                                    title="Lighting & Atmosphere" icon="💡" color="text-indigo-300"
                                    value={result.analyzed_structure.lighting_and_atmosphere}
                                    onSave={(val) => saveField('lighting_and_atmosphere', val)}
                                    saving={savingField === 'lighting_and_atmosphere'}
                                    fullWidth
                                />
                            </div>
                        )}

                        {/* REFINE WITH AI */}
                        <div className="border-t border-gray-800 pt-4">
                            {!refineOpen ? (
                                <button
                                    type="button"
                                    onClick={() => setRefineOpen(true)}
                                    disabled={refineDisabled}
                                    className="w-full py-2.5 px-4 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed text-gray-200 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-2"
                                >
                                    <Wand2 className="w-4 h-4 text-violet-400" />
                                    {refineDisabled
                                        ? `Đã đạt giới hạn tinh chỉnh (${MAX_REFINEMENTS}/${MAX_REFINEMENTS})`
                                        : `Tinh chỉnh bằng AI (${refinementCount}/${MAX_REFINEMENTS})`}
                                </button>
                            ) : (
                                <div className="bg-gray-950/60 border border-gray-800 rounded-xl p-4 space-y-3">
                                    <div>
                                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                                            Chọn field cần làm rõ hơn
                                        </label>
                                        <div className="flex flex-wrap gap-1.5 mt-1.5">
                                            {[{ key: 'all', label: 'Tất cả' }, ...REFINABLE_FIELDS].map((f) => (
                                                <button
                                                    type="button"
                                                    key={f.key}
                                                    onClick={() => toggleRefineField(f.key)}
                                                    className={`text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition ${
                                                        refineFields.includes(f.key)
                                                            ? 'bg-violet-600 text-white border-violet-500'
                                                            : 'bg-gray-900 text-gray-400 border-gray-700 hover:border-gray-600'
                                                    }`}
                                                >
                                                    {f.label}
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    <div>
                                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                                            Yêu cầu cụ thể
                                        </label>
                                        <textarea
                                            rows={3}
                                            value={refineInstruction}
                                            onChange={(e) => setRefineInstruction(e.target.value)}
                                            placeholder='VD: "Camera movement hiện quá chung chung, hãy mô tả rõ kiểu chuyển động (dolly-in, orbit, crane shot...) và tốc độ."'
                                            className="w-full mt-1.5 bg-gray-900 border border-gray-700 rounded-lg p-2.5 text-xs text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-y"
                                        />
                                    </div>

                                    {refineError && (
                                        <p className="text-xs text-rose-400 flex items-center gap-1.5">
                                            <AlertCircle className="w-3.5 h-3.5" />
                                            {refineError}
                                        </p>
                                    )}

                                    <div className="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setRefineOpen(false)}
                                            disabled={refineSubmitting}
                                            className="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-lg transition"
                                        >
                                            Hủy
                                        </button>
                                        <button
                                            type="button"
                                            onClick={submitRefine}
                                            disabled={refineSubmitting || !refineInstruction.trim()}
                                            className="px-4 py-1.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5"
                                        >
                                            <RefreshCcw className={`w-3.5 h-3.5 ${refineSubmitting ? 'animate-spin' : ''}`} />
                                            Gửi yêu cầu tinh chỉnh
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* FOOTER */}
            <div className="pt-4 border-t border-gray-800 flex items-center justify-between text-[11px] text-gray-500">
                <span>
                    Status ID: {activeId || 'None'}
                </span>

                <span>Engine Response: HTTP 200 OK</span>
            </div>
        </div>
    );
}