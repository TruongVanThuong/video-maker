import React, { useRef, useState } from 'react';
import axios from 'axios';
import { X, FlaskConical, Play, CheckCircle2, AlertCircle, Sparkles, Copy, Check } from 'lucide-react';
import { PromptTemplate } from '@/types/promptTemplate';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    template: PromptTemplate | null;
}

export default function TemplateTestDrawer({ isOpen, onClose, template }: Props) {
    const [sampleInput, setSampleInput] = useState(
        'A futuristic samurai standing in front of a glowing cybernetic temple under heavy rain.'
    );
    const [systemInstruction, setSystemInstruction] = useState(
        template?.system_instruction || ''
    );
    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState<Record<string, unknown> | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [copied, setCopied] = useState(false);
    const abortRef = useRef<AbortController | null>(null);

    React.useEffect(() => {
        if (template) {
            setSystemInstruction(template.system_instruction);
            setSampleInput((prev) => prev);
            setTestResult(null);
            setError(null);
        }
    }, [template?.id]);

    if (!isOpen || !template) return null;

    const handleRunTest = async () => {
        setTesting(true);
        setError(null);
        setTestResult(null);

        const controller = new AbortController();
        abortRef.current = controller;

        try {
            const response = await axios.post('/prompt-templates/test', {
                system_instruction: systemInstruction,
                sample_input: sampleInput,
            });

            if (response.data.success) {
                setTestResult(response.data.result);
            } else {
                setError(response.data.error || 'Failed to generate test output.');
            }
        } catch (err: unknown) {
            if (axios.isAxiosError(err)) {
                setError(err.response?.data?.error || err.message || 'Network error.');
            } else {
                setError('An unknown error occurred.');
            }
        } finally {
            setTesting(false);
        }
    };

    const handleClose = () => {
        abortRef.current?.abort();
        onClose();
    };

    const handleCopyResult = () => {
        if (!testResult) return;
        const text = (testResult.final_video_prompt as string) || JSON.stringify(testResult, null, 2);
        navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <div className="fixed inset-0 z-50 overflow-hidden bg-black/70 backdrop-blur-sm flex justify-end">
            <div className="bg-gray-900 border-l border-gray-800 w-full max-w-xl h-full shadow-2xl flex flex-col justify-between overflow-y-auto">
                
                {/* Header */}
                <div className="p-5 border-b border-gray-800 bg-gray-950/80 flex items-center justify-between sticky top-0 z-10">
                    <div className="flex items-center space-x-2.5">
                        <div className="p-2 bg-emerald-600/20 text-emerald-400 rounded-xl border border-emerald-500/30">
                            <FlaskConical className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                Template Live Playground
                            </h3>
                            <p className="text-xs text-gray-400">
                                Testing: <strong className="text-emerald-300">{template.name}</strong>
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleClose}
                        className="p-1.5 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Content Body */}
                <div className="p-6 space-y-6 flex-1 overflow-y-auto">

                    {/* System Instruction Box */}
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <Sparkles className="w-4 h-4 text-violet-400" />
                            System Instruction Rules
                        </label>
                        <textarea
                            rows={4}
                            value={systemInstruction}
                            onChange={(e) => setSystemInstruction(e.target.value)}
                            className="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-gray-200 font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>

                    {/* Sample Input Text */}
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                            Sample Content Script
                        </label>
                        <textarea
                            rows={3}
                            value={sampleInput}
                            onChange={(e) => setSampleInput(e.target.value)}
                            placeholder="Enter test script..."
                            className="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>

                    {/* Run Test Button */}
                    <button
                        onClick={handleRunTest}
                        disabled={testing || !sampleInput.trim()}
                        className="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/20 disabled:opacity-50 transition flex items-center justify-center space-x-2"
                    >
                        {testing ? (
                            <>
                                <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                <span>Running AI Test with Gemini...</span>
                            </>
                        ) : (
                            <>
                                <Play className="w-4 h-4 fill-white" />
                                <span>Execute Live AI Test</span>
                            </>
                        )}
                    </button>

                    {/* Test Error */}
                    {error && (
                        <div className="p-4 bg-rose-950/40 border border-rose-800 rounded-xl text-xs text-rose-300 flex items-start gap-2">
                            <AlertCircle className="w-4 h-4 text-rose-400 shrink-0 mt-0.5" />
                            <span>{error}</span>
                        </div>
                    )}

                    {/* Test Output Result */}
                    {testResult && (
                        <div className="space-y-3 bg-gray-950 p-4 rounded-xl border border-emerald-900/60 shadow-lg">
                            <div className="flex items-center justify-between border-b border-gray-800 pb-2">
                                <span className="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                    <CheckCircle2 className="w-4 h-4" /> AI JSON Output Result
                                </span>
                                <button
                                    onClick={handleCopyResult}
                                    className="text-xs px-2.5 py-1 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition flex items-center gap-1"
                                >
                                    {copied ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                                    <span>{copied ? 'Copied' : 'Copy'}</span>
                                </button>
                            </div>

                            {typeof testResult.final_video_prompt === 'string' && (
                                <div className="p-3 bg-gray-900 rounded-lg border border-gray-800 text-xs font-mono text-emerald-300 leading-relaxed">
                                    <strong>Final Video Prompt:</strong>
                                    <p className="mt-1">{testResult.final_video_prompt}</p>
                                </div>
                            )}

                            <pre className="p-3 bg-gray-900 text-green-400 rounded-lg border border-gray-800 text-xs overflow-x-auto font-mono max-h-60">
                                {JSON.stringify(testResult, null, 2)}
                            </pre>
                        </div>
                    )}

                </div>

                {/* Footer */}
                <div className="p-4 border-t border-gray-800 bg-gray-950/50 flex justify-end">
                    <button
                        onClick={handleClose}
                        className="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-xl transition"
                    >
                        Close Playground
                    </button>
                </div>

            </div>
        </div>
    );
}
