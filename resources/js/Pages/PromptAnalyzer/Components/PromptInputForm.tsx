import { FormEvent } from 'react';
import {
    Wand2,
    Layers,
    MonitorPlay,
    FileText,
    ChevronDown,
    AlertCircle,
    X,
    Zap,
} from 'lucide-react';

export interface PromptTemplate {
    id: number;
    name: string;
    category: string;
}

export interface PlatformOption {
    id: string;
    name: string;
    badge: string;
    color: string;
    description: string;
}

export interface PromptFormData {
    input_content: string;
    prompt_template_id: number | '';
    target_platform: string;
}

interface PromptInputFormProps {
    data: PromptFormData;
    setData: <K extends keyof PromptFormData>(
        key: K,
        value: PromptFormData[K]
    ) => void;

    errors: Partial<Record<keyof PromptFormData, string>>;
    processing: boolean;
    isProcessing: boolean;

    templates: PromptTemplate[];
    categories: string[];
    selectedCategory: string;
    setSelectedCategory: (category: string) => void;

    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
}

const PLATFORM_OPTIONS: PlatformOption[] = [
    {
        id: 'runway',
        name: 'Runway Gen-3',
        badge: 'Gen-3',
        color: 'from-purple-500 to-indigo-600',
        description: 'Cinematic movement & high physics fidelity',
    },
    {
        id: 'sora',
        name: 'OpenAI Sora',
        badge: 'Sora',
        color: 'from-emerald-500 to-teal-600',
        description: 'Complex multi-shot consistency & realism',
    },
    {
        id: 'kling',
        name: 'Kling AI',
        badge: 'Kling 1.5',
        color: 'from-amber-500 to-orange-600',
        description: 'Fluid character action & dynamic angles',
    },
    {
        id: 'midjourney',
        name: 'Midjourney v6',
        badge: 'MJ v6',
        color: 'from-blue-500 to-cyan-600',
        description: 'Aesthetic visual styling & atmospheric cues',
    },
];

const PRESET_EXAMPLES = [
    {
        title: 'Cyberpunk Rain City',
        text: 'A cybernetic detective wearing a illuminated neon trench coat walks down a rainy cyberpunk alleyway in Neo-Tokyo, reflection of flickering holographic ads in puddles, cinematic 8k, slow pan camera.',
    },
    {
        title: 'Sci-Fi Spaceship Chase',
        text: 'An agile futuristic starfighter maneuvers through an asteroid belt at breakneck speed, engine plasma trail glowing bright cyan, dramatic dynamic tracking shot with lens flare.',
    },
    {
        title: 'Fantasy Dragon Flight',
        text: 'A majestic emerald dragon soaring majestically above mist-covered mountain peaks at golden hour sunset, realistic wing physics, sweeping aerial drone shot, atmospheric volumetric rays.',
    },
];

export default function PromptInputForm({
    data,
    setData,
    errors,
    processing,
    isProcessing,
    templates,
    categories,
    selectedCategory,
    setSelectedCategory,
    onSubmit,
}: PromptInputFormProps) {
    const filteredTemplates =
        selectedCategory === 'all'
            ? templates
            : templates.filter(
                  (template) => template.category === selectedCategory
              );

    const applyPreset = (presetText: string) => {
        setData('input_content', presetText);
    };

    return (
        <div className="lg:col-span-5 bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-6 shadow-2xl space-y-6">
            {/* HEADER */}
            <div className="flex items-center justify-between border-b border-gray-800 pb-4">
                <h2 className="text-lg font-bold text-white flex items-center gap-2">
                    <Wand2 className="w-5 h-5 text-indigo-400" />
                    Prompt Generator
                </h2>

                <span className="text-xs text-gray-400 bg-gray-800 px-2.5 py-1 rounded-lg border border-gray-700">
                    Step 1 of 2
                </span>
            </div>

            <form onSubmit={onSubmit} className="space-y-6">
                {/* STYLE TEMPLATE */}
                <div className="space-y-2">
                    <div className="flex justify-between items-center flex-wrap gap-2">
                        <label
                            htmlFor="prompt_template_id"
                            className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5"
                        >
                            <Layers className="w-4 h-4 text-violet-400" />
                            Style Template
                        </label>

                        <div className="flex gap-2 overflow-x-auto overflow-y-hidden whitespace-nowrap pb-1 scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent hover:scrollbar-thumb-gray-500">
                            {categories.map((category) => (
                                <button
                                    type="button"
                                    key={category}
                                    onClick={() =>
                                        setSelectedCategory(category)
                                    }
                                    disabled={isProcessing}
                                    className={`text-[10px] uppercase font-semibold px-2 py-0.5 rounded-full transition ${
                                        selectedCategory === category
                                            ? 'bg-violet-600 text-white'
                                            : 'bg-gray-800 text-gray-400 hover:text-gray-200'
                                    }`}
                                >
                                    {category}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="relative">
                        <select
                            id="prompt_template_id"
                            value={data.prompt_template_id}
                            onChange={(event) =>
                                setData(
                                    'prompt_template_id',
                                    event.target.value
                                        ? Number(event.target.value)
                                        : ''
                                )
                            }
                            disabled={isProcessing}
                            className="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2.5 text-sm text-gray-200 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 disabled:opacity-50 transition appearance-none cursor-pointer pr-10"
                        >
                            <option value="">
                                ✨ Auto AI Default Style (Balanced)
                            </option>

                            {filteredTemplates.map((template) => (
                                <option
                                    key={template.id}
                                    value={template.id}
                                >
                                    {template.name} [
                                    {template.category.toUpperCase()}]
                                </option>
                            ))}
                        </select>

                        <ChevronDown className="w-4 h-4 text-gray-400 absolute right-3 top-3.5 pointer-events-none" />
                    </div>

                    {errors.prompt_template_id && (
                        <p className="text-xs text-rose-400 mt-1 flex items-center gap-1">
                            <AlertCircle className="w-3.5 h-3.5" />
                            {errors.prompt_template_id}
                        </p>
                    )}
                </div>

                {/* TARGET PLATFORM */}
                <div className="space-y-2">
                    <label className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                        <MonitorPlay className="w-4 h-4 text-indigo-400" />
                        Target Video Platform
                    </label>

                    <div className="grid grid-cols-2 gap-2">
                        {PLATFORM_OPTIONS.map((platform) => {
                            const isSelected =
                                data.target_platform === platform.id;

                            return (
                                <button
                                    type="button"
                                    key={platform.id}
                                    onClick={() =>
                                        setData(
                                            'target_platform',
                                            platform.id
                                        )
                                    }
                                    disabled={isProcessing}
                                    className={`p-3 rounded-xl border text-left transition relative overflow-hidden group ${
                                        isSelected
                                            ? 'bg-gradient-to-r from-violet-900/40 to-indigo-900/40 border-violet-500 shadow-md shadow-violet-500/10'
                                            : 'bg-gray-950/60 border-gray-800 hover:border-gray-700 hover:bg-gray-800/40'
                                    }`}
                                >
                                    <div className="flex items-center justify-between mb-1">
                                        <span
                                            className={`text-xs font-bold ${
                                                isSelected
                                                    ? 'text-white'
                                                    : 'text-gray-300'
                                            }`}
                                        >
                                            {platform.name}
                                        </span>

                                        <span
                                            className={`text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-gradient-to-r ${platform.color} text-white shadow-sm`}
                                        >
                                            {platform.badge}
                                        </span>
                                    </div>

                                    <p className="text-[11px] text-gray-400 line-clamp-1">
                                        {platform.description}
                                    </p>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* INPUT CONTENT */}
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <label
                            htmlFor="input_content"
                            className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5"
                        >
                            <FileText className="w-4 h-4 text-blue-400" />
                            Input Script / Scene Description
                        </label>

                        <span className="text-xs text-gray-400">
                            {data.input_content.length} / 10,000
                        </span>
                    </div>

                    <div className="relative">
                        <textarea
                            id="input_content"
                            rows={6}
                            value={data.input_content}
                            onChange={(event) =>
                                setData(
                                    'input_content',
                                    event.target.value
                                )
                            }
                            disabled={isProcessing}
                            maxLength={10000}
                            placeholder="Describe your scene, story idea, camera movement, or raw text here in English or Vietnamese..."
                            className="w-full bg-gray-950 border border-gray-800 rounded-xl p-3.5 text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 disabled:opacity-50 transition resize-y font-sans"
                        />

                        {data.input_content && !isProcessing && (
                            <button
                                type="button"
                                onClick={() =>
                                    setData('input_content', '')
                                }
                                className="absolute right-3 top-3 p-1 rounded-md text-gray-400 hover:text-white bg-gray-800/80 hover:bg-gray-700 transition"
                                title="Clear content"
                            >
                                <X className="w-3.5 h-3.5" />
                            </button>
                        )}
                    </div>

                    {errors.input_content && (
                        <p className="text-xs text-rose-400 flex items-center gap-1">
                            <AlertCircle className="w-3.5 h-3.5" />
                            {errors.input_content}
                        </p>
                    )}

                    {/* PRESETS */}
                    <div className="pt-2">
                        <p className="text-[11px] font-semibold text-gray-400 mb-1.5">
                            Quick Sample Presets:
                        </p>

                        <div className="flex flex-wrap gap-1.5">
                            {PRESET_EXAMPLES.map((preset) => (
                                <button
                                    type="button"
                                    key={preset.title}
                                    onClick={() =>
                                        applyPreset(preset.text)
                                    }
                                    disabled={isProcessing}
                                    className="text-xs bg-gray-800/80 hover:bg-violet-950/60 hover:text-violet-300 text-gray-300 px-2.5 py-1 rounded-lg border border-gray-700/60 hover:border-violet-700/60 transition flex items-center gap-1"
                                >
                                    <Zap className="w-3 h-3 text-amber-400" />
                                    {preset.title}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* SUBMIT */}
                <button
                    type="submit"
                    disabled={
                        processing ||
                        isProcessing ||
                        !data.input_content.trim()
                    }
                    className="w-full py-3.5 px-4 bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/25 hover:shadow-indigo-500/40 disabled:opacity-50 disabled:cursor-not-allowed transition transform active:scale-[0.99] flex items-center justify-center space-x-2"
                >
                    {isProcessing ? (
                        <>
                            <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            <span>Analyzing Script with AI...</span>
                        </>
                    ) : (
                        <>
                            <Wand2 className="w-4 h-4" />
                            <span>
                                Analyze & Generate Video Prompt
                            </span>
                        </>
                    )}
                </button>
            </form>
        </div>
    );
}