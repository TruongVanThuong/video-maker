import {
    Clock,
    ArrowUpRight,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import { router } from '@inertiajs/react';
import {
    PromptStatus,
    RecentPrompt,
    PromptPaginationMeta,
} from '@/types/prompt';
import { t } from '@/i18n';

interface PromptHistoryListProps {
    history: RecentPrompt[];
    pagination: PromptPaginationMeta;
    activeId: number | null;
    onSelect: (item: RecentPrompt) => void;
}

function getStatusClass(status: PromptStatus) {
    switch (status) {
        case 'completed':
            return 'bg-emerald-950 text-emerald-400 border border-emerald-800/60';

        case 'failed':
            return 'bg-rose-950 text-rose-400 border border-rose-800/60';

        case 'processing':
        case 'pending':
            return 'bg-amber-950 text-amber-400 border border-amber-800/60';

        default:
            return 'bg-gray-800 text-gray-400 border border-gray-700';
    }
}

export default function PromptHistoryList({
    history,
    pagination,
    activeId,
    onSelect,
}: PromptHistoryListProps) {
    // Các link phân trang của Laravel: label dạng số là các trang,
    // còn lại là Previous/Next nên được xử lý riêng.
    const pageLinks = pagination.links.filter((link) =>
        /^\d+$/.test(link.label.trim())
    );

    const goToPage = (url: string | null) => {
        if (!url) return;

        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <div className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-6 shadow-2xl space-y-4">
            {/* HEADER */}
            <div className="flex items-center justify-between border-b border-gray-800 pb-3">
                <div className="flex items-center space-x-2">
                    <Clock className="w-5 h-5 text-violet-400" />

                    <h3 className="text-lg font-bold text-white">
                        {t('analyzer.history.title')}
                    </h3>
                </div>

                <span className="text-xs text-gray-400">
                    {t('analyzer.history.showing', {
                        count: pagination.total,
                    })}
                </span>
            </div>

            {/* EMPTY */}
            {history.length === 0 ? (
                <div className="py-8 text-center text-xs text-gray-500">
                    {t('analyzer.history.empty')}
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {history.map((item) => {
                        const isSelected = activeId === item.id;

                        return (
                            <button
                                type="button"
                                key={item.id}
                                onClick={() => onSelect(item)}
                                className={`p-4 rounded-xl border cursor-pointer transition text-left flex flex-col justify-between space-y-3 group ${
                                    isSelected
                                        ? 'bg-violet-950/40 border-violet-500 shadow-md shadow-violet-500/10'
                                        : 'bg-gray-950/60 border-gray-800/80 hover:border-gray-700 hover:bg-gray-800/40'
                                }`}
                            >
                                <div className="space-y-1.5">
                                    <div className="flex items-center justify-between">
                                        <span
                                            className={`text-[10px] font-extrabold uppercase px-2 py-0.5 rounded ${getStatusClass(
                                                item.status
                                            )}`}
                                        >
                                            {t(`status.${item.status}`)}
                                        </span>

                                        <span className="text-[11px] text-gray-500">
                                            #{item.id}
                                        </span>
                                    </div>

                                    <p className="text-xs font-semibold text-gray-200 line-clamp-2 leading-relaxed">
                                        {item.input_content}
                                    </p>
                                </div>

                                <div className="pt-2 border-t border-gray-800/60 flex items-center justify-between text-[11px] text-gray-400">
                                    <span className="truncate max-w-[150px]">
                                        {item.final_prompt
                                            ? item.final_prompt
                                            : t('analyzer.history.no_final')}
                                    </span>

                                    <span className="text-violet-400 font-semibold flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                                        {t('analyzer.history.view')}
                                        <ArrowUpRight className="w-3 h-3" />
                                    </span>
                                </div>
                            </button>
                        );
                    })}
                </div>
            )}

            {/* PAGINATION */}
            {pagination.last_page > 1 && (
                <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-800">
                    <span className="text-[11px] text-gray-500">
                        {t('analyzer.history.page', {
                            current: pagination.current_page,
                            total: pagination.last_page,
                        })}
                    </span>

                    <div className="flex items-center gap-1.5">
                        <button
                            type="button"
                            onClick={() =>
                                goToPage(pagination.prev_page_url)
                            }
                            disabled={!pagination.prev_page_url}
                            className="px-2.5 py-1.5 rounded-xl text-xs font-bold transition bg-gray-900 border border-gray-800 text-gray-300 hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-gray-900 flex items-center gap-1"
                        >
                            <ChevronLeft className="w-3.5 h-3.5" />
                            <span>{t('analyzer.history.prev')}</span>
                        </button>

                        {pageLinks.map((link, index) => (
                            <button
                                type="button"
                                key={`${link.label}-${index}`}
                                onClick={() => goToPage(link.url)}
                                disabled={!link.url || link.active}
                                className={`min-w-[2rem] px-2.5 py-1.5 rounded-xl text-xs font-bold transition ${
                                    link.active
                                        ? 'bg-violet-600 text-white shadow-md shadow-violet-600/20'
                                        : link.url
                                        ? 'bg-gray-900 border border-gray-800 text-gray-300 hover:bg-gray-800'
                                        : 'bg-gray-950 border border-gray-900 text-gray-600 cursor-not-allowed'
                                }`}
                            >
                                {link.label}
                            </button>
                        ))}

                        <button
                            type="button"
                            onClick={() =>
                                goToPage(pagination.next_page_url)
                            }
                            disabled={!pagination.next_page_url}
                            className="px-2.5 py-1.5 rounded-xl text-xs font-bold transition bg-gray-900 border border-gray-800 text-gray-300 hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-gray-900 flex items-center gap-1"
                        >
                            <span>{t('analyzer.history.next')}</span>
                            <ChevronRight className="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
