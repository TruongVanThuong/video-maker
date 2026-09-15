import React, {
    FormEvent,
    useEffect,
    useState,
} from 'react';

import {
    Head,
    useForm,
    usePage,
    Link,
} from '@inertiajs/react';

import {
    Wand2,
    SlidersHorizontal,
    Cpu,
    CheckCircle2,
} from 'lucide-react';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

import {
    usePromptPolling,
} from '@/Hooks/usePromptPolling';

import PromptInputForm, {
    PromptTemplate,
    PromptFormData,
} from './Components/PromptInputForm';

import PromptOutputPanel, {
    PromptResult,
    PromptStatus,
} from './Components/PromptOutputPanel';

import PromptHistoryList, {
    RecentPrompt,
} from './Components/PromptHistoryList';

interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };

    templates: PromptTemplate[];

    recentPrompts: RecentPrompt[];

    flash?: {
        prompt_id?: number;
    };

    [key: string]: unknown;
}

export default function PromptAnalyzerIndex() {
    const {
        templates = [],
        recentPrompts = [],
        flash,
    } = usePage<PageProps>().props;

    /*
    | Prompt Polling
    */

    const {
        status,
        result,
        activeId,
        selectPromptResult,
        markAsRefining,
    } = usePromptPolling(flash?.prompt_id);

    const [liveResult, setLiveResult] = useState<PromptResult | null>(null);

    // đồng bộ khi result từ polling thay đổi, giữ liveResult là nguồn hiển thị chính
    useEffect(() => {
        if (result) setLiveResult(result);
    }, [result]);
    /*
    | Local State
    */

    const [pipelineSent, setPipelineSent] =
        useState(false);

    const [selectedCategory, setSelectedCategory] =
        useState<string>('all');

    const [historyList, setHistoryList] =
        useState<RecentPrompt[]>(recentPrompts);

    /*
    | Form
    */

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm<PromptFormData>({
        input_content: '',
        prompt_template_id: '',
        target_platform: 'runway',
    });

    /*
    | Derived State
    */

    const isProcessing =
        processing ||
        status === 'processing' ||
        status === 'pending';

    const categories = [
        'all',
        ...Array.from(
            new Set(
                templates.map(
                    (template) => template.category
                )
            )
        ),
    ];

    const completedCount =
        historyList.filter(
            (prompt) => prompt.status === 'completed'
        ).length;

    /*
    | Update History
    */

    useEffect(() => {
        if (
            !result ||
            (result.status !== 'completed' &&
                result.status !== 'failed')
        ) {
            return;
        }

        setHistoryList((previousHistory) => {
            const existingIndex =
                previousHistory.findIndex(
                    (item) => item.id === result.id
                );

            /*
            | Existing history item
            */

            if (existingIndex >= 0) {
                const updatedHistory = [
                    ...previousHistory,
                ];

                updatedHistory[existingIndex] = {
                    ...updatedHistory[existingIndex],

                    status: result.status,

                    final_prompt:
                        result.final_prompt,

                    analyzed_structure:
                        result.analyzed_structure,
                };

                return updatedHistory;
            }

            /*
            | New history item
            */

            const newItem: RecentPrompt = {
                id: result.id,

                input_content:
                    data.input_content ||
                    result.input_content ||
                    'Analyzed Prompt',

                status: result.status,

                analyzed_structure:
                    result.analyzed_structure,

                final_prompt:
                    result.final_prompt,

                target_platform:
                    data.target_platform,

                created_at: 'Just now',
            };

            return [
                newItem,
                ...previousHistory,
            ];
        });
    }, [result]);

    /*
    | Submit Prompt
    */

    const handleSubmit = (
        event: FormEvent<HTMLFormElement>
    ) => {
        event.preventDefault();

        setPipelineSent(false);

        post('/prompts', {
            preserveScroll: true,
        });
    };

    /*
    | Retry
    */

    const handleRetry = () => {
        /*
         * Reuse the same form data.
         *
         * The PromptInputForm only knows that
         * it should call this function.
         */
        post('/prompts', {
            preserveScroll: true,
        });
    };

    /*
    | Select History
    */

    const handleSelectHistory = (
        item: RecentPrompt
    ) => {
        selectPromptResult(item);
    };

    /*
    | Send To Video Pipeline
    */

    const handleSendPipeline = () => {
        /*
         * At the moment this is only a UI state.
         *
         * Later this should call:
         *
         * POST /projects/{project}/video-pipeline
         *
         * or another dedicated backend endpoint.
         */

        setPipelineSent(true);

        window.setTimeout(() => {
            setPipelineSent(false);
        }, 3000);
    };

    /*
    | Render
    */

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    {/* PAGE TITLE */}
                    <div>
                        <div className="flex items-center space-x-3">
                            <div className="p-2 bg-gradient-to-tr from-violet-600 to-indigo-500 rounded-xl shadow-lg shadow-indigo-500/20 text-white">
                                <Wand2 className="w-6 h-6 animate-pulse" />
                            </div>

                            <div>
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                                    AI Video Prompt Studio

                                    <span className="text-xs px-2.5 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300 font-semibold border border-violet-200 dark:border-violet-700/50">
                                        v2.0
                                    </span>
                                </h1>

                                <p className="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    Deconstruct text scripts into
                                    ultra-precise camera & scene
                                    instructions
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* STATS & ACTIONS */}
                    <div className="flex items-center space-x-3">
                        <div className="hidden lg:flex items-center space-x-4 px-3 py-1.5 bg-gray-100 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 text-xs">
                            <div className="flex items-center space-x-1.5 text-gray-600 dark:text-gray-300">
                                <Cpu className="w-3.5 h-3.5 text-emerald-500" />

                                <span>
                                    Engine:{' '}
                                    <strong className="text-gray-900 dark:text-white">
                                        Gemini 2.5 Flash
                                    </strong>
                                </span>
                            </div>

                            <div className="h-3 w-px bg-gray-300 dark:bg-gray-700" />

                            <div className="flex items-center space-x-1.5 text-gray-600 dark:text-gray-300">
                                <CheckCircle2 className="w-3.5 h-3.5 text-indigo-500" />

                                <span>
                                    Generated:{' '}
                                    <strong className="text-gray-900 dark:text-white">
                                        {completedCount}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <Link
                            href={route(
                                'prompt-templates.index'
                            )}
                            className="inline-flex items-center space-x-2 px-3.5 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-xs font-semibold rounded-xl transition shadow-sm border border-gray-700"
                        >
                            <SlidersHorizontal className="w-4 h-4" />

                            <span>
                                Manage Templates
                            </span>
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="AI Video Prompt Studio" />

            <div className="py-8 bg-gray-950 min-h-[calc(100vh-4rem)] text-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                    {/* MAIN WORKSPACE */}
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        {/* INPUT */}
                        <PromptInputForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            processing={processing}
                            isProcessing={isProcessing}
                            templates={templates}
                            categories={categories}
                            selectedCategory={
                                selectedCategory
                            }
                            setSelectedCategory={
                                setSelectedCategory
                            }
                            onSubmit={handleSubmit}
                        />

                        {/* OUTPUT */}
                        <PromptOutputPanel
                            status={status as PromptStatus}
                            result={liveResult}
                            activeId={activeId}
                            onRetry={handleRetry}
                            onSendPipeline={handleSendPipeline}
                            onRefineStart={markAsRefining}
                            onResultUpdated={setLiveResult}
                        />
                    </div>

                    {/* HISTORY */}
                    <PromptHistoryList
                        history={historyList}
                        activeId={activeId}
                        onSelect={
                            handleSelectHistory
                        }
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}