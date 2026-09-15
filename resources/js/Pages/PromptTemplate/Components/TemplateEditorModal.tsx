import React, { FormEvent, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { X, Sparkles, AlertCircle, Save, Info } from 'lucide-react';
import { PRESET_TEMPLATE_CATEGORIES } from '@/constants/promptTemplateCategories';
import { PromptTemplate } from '@/types/promptTemplate';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    template?: PromptTemplate | null;
    categories: string[];
}

interface FormData {
    name: string;
    category: string;
    system_instruction: string;
    is_active: boolean;
}

const PLACEHOLDER_VARIABLES = [
    { tag: '{subject}', desc: 'Main subject or character' },
    { tag: '{action}', desc: 'Subject motion or activity' },
    { tag: '{environment}', desc: 'Setting, weather, surrounding' },
    { tag: '{camera_movement}', desc: 'Pan, zoom, orbit, tracking shot' },
    { tag: '{lighting}', desc: 'Atmosphere, color grading, golden hour' },
];

export default function TemplateEditorModal({ isOpen, onClose, template, categories }: Props) {
    const isEditing = !!template;

    const combinedCategories = Array.from(new Set([...PRESET_TEMPLATE_CATEGORIES, ...categories]));

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm<FormData>({
        name: '',
        category: 'cinematic',
        system_instruction: '',
        is_active: true,
    });

    useEffect(() => {
        if (template) {
            setData({
                name: template.name,
                category: template.category,
                system_instruction: template.system_instruction,
                is_active: template.is_active,
            });
        } else {
            reset();
        }
        clearErrors();
    }, [template, isOpen]);

    if (!isOpen) return null;

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing && template) {
            put(route('prompt-templates.update', template.id), {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        } else {
            post(route('prompt-templates.store'), {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        }
    };

    const injectVariable = (varTag: string) => {
        setData('system_instruction', data.system_instruction + ' ' + varTag);
    };

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-md flex items-center justify-center p-4">
            <div className="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden transform transition-all">
                
                {/* Header */}
                <div className="flex items-center justify-between p-5 border-b border-gray-800 bg-gray-950/50">
                    <div className="flex items-center space-x-2.5">
                        <div className="p-2 bg-violet-600/20 text-violet-400 rounded-xl border border-violet-500/30">
                            <Sparkles className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="text-base font-bold text-white">
                                {isEditing ? 'Edit Prompt Template' : 'Create New Prompt Template'}
                            </h3>
                            <p className="text-xs text-gray-400">
                                Set rule constraints to guide LLM prompt generation logic
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="p-1.5 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Form Body */}
                <form onSubmit={handleSubmit} className="p-6 space-y-5">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {/* Name */}
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                                Template Name
                            </label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="e.g. Cinematic 8K Sci-Fi"
                                className="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2 text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition"
                            />
                            {errors.name && (
                                <p className="text-xs text-rose-400 flex items-center gap-1">
                                    <AlertCircle className="w-3 h-3" /> {errors.name}
                                </p>
                            )}
                        </div>

                        {/* Category */}
                        <div className="space-y-1.5">
                            <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                                Category
                            </label>
                            <select
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                                className="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2 text-sm text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition capitalize cursor-pointer"
                            >
                                {combinedCategories.map((cat) => (
                                    <option key={cat} value={cat}>
                                        {cat}
                                    </option>
                                ))}
                            </select>
                            {errors.category && (
                                <p className="text-xs text-rose-400 flex items-center gap-1">
                                    <AlertCircle className="w-3 h-3" /> {errors.category}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* System Instruction */}
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <label className="text-xs font-bold text-gray-300 uppercase tracking-wider">
                                System Instruction Prompt
                            </label>
                            <span className="text-xs text-gray-400 font-mono">
                                {data.system_instruction.length} chars
                            </span>
                        </div>
                        <textarea
                            rows={6}
                            value={data.system_instruction}
                            onChange={(e) => setData('system_instruction', e.target.value)}
                            placeholder="Specify rules for the AI (e.g. Always output cinematic lighting details, emphasize high-contrast camera pan, format output strictly in English...)"
                            className="w-full bg-gray-950 border border-gray-800 rounded-xl p-3.5 text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition font-mono leading-relaxed resize-y"
                        />
                        {errors.system_instruction && (
                            <p className="text-xs text-rose-400 flex items-center gap-1">
                                <AlertCircle className="w-3 h-3" /> {errors.system_instruction}
                            </p>
                        )}
                    </div>

                    {/* Dynamic Variables Helper Box */}
                    <div className="p-3.5 bg-gray-950/80 rounded-xl border border-gray-800/80 space-y-2">
                        <div className="flex items-center text-xs font-bold text-violet-300 gap-1.5">
                            <Info className="w-3.5 h-3.5 text-violet-400" />
                            <span>Quick Variable Injection Guide</span>
                        </div>
                        <div className="flex flex-wrap gap-1.5">
                            {PLACEHOLDER_VARIABLES.map((v) => (
                                <button
                                    type="button"
                                    key={v.tag}
                                    onClick={() => injectVariable(v.tag)}
                                    className="text-xs bg-gray-800 hover:bg-violet-900/60 hover:text-violet-200 text-gray-300 px-2 py-1 rounded-lg border border-gray-700 transition"
                                    title={v.desc}
                                >
                                    + {v.tag}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Active Status Toggle */}
                    <div className="flex items-center justify-between p-3 bg-gray-950/50 rounded-xl border border-gray-800">
                        <div>
                            <span className="text-xs font-bold text-gray-200">Active Status</span>
                            <p className="text-[11px] text-gray-400">Available for selection in Prompt Studio</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setData('is_active', !data.is_active)}
                            className={`w-12 h-6 rounded-full transition-colors relative p-1 ${
                                data.is_active ? 'bg-emerald-600' : 'bg-gray-700'
                            }`}
                        >
                            <span
                                className={`w-4 h-4 rounded-full bg-white block transition-transform ${
                                    data.is_active ? 'translate-x-6' : 'translate-x-0'
                                }`}
                            />
                        </button>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex items-center justify-end space-x-3 pt-3 border-t border-gray-800">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-xl transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-5 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center space-x-1.5"
                        >
                            <Save className="w-4 h-4" />
                            <span>{isEditing ? 'Save Changes' : 'Create Template'}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    );
}
