import React, { useState } from 'react';
import { Head, usePage, router, Link } from '@inertiajs/react';
import {
    Plus,
    Search,
    Edit3,
    Copy,
    FlaskConical,
    Trash2,
    Layers,
    Wand2,
    CheckCircle2,
    XCircle,
    FileText,
} from 'lucide-react';
import { PromptTemplate } from '@/types/promptTemplate';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TemplateEditorModal from './Components/TemplateEditorModal';
import TemplateTestDrawer from './Components/TemplateTestDrawer';
import DeleteConfirmModal from './Components/DeleteConfirmModal';
import { PRESET_TEMPLATE_CATEGORIES } from '@/constants/promptTemplateCategories';

interface PaginatedTemplates {
    data: PromptTemplate[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    templates: PaginatedTemplates;
    categories: string[];
    filters: {
        category?: string | null;
        search?: string | null;
    };
    flash?: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export default function PromptTemplateIndex() {
    const { auth, templates, categories = [], filters = {} } = usePage<PageProps>().props;

    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState<string>(filters.category || 'all');
    const [expandedTemplateId, setExpandedTemplateId] = useState<number | null>(null);

    // Modal / Drawer States
    const [editorModalOpen, setEditorModalOpen] = useState(false);
    const [editingTemplate, setEditingTemplate] = useState<PromptTemplate | null>(null);

    const [testDrawerOpen, setTestDrawerOpen] = useState(false);
    const [testingTemplate, setTestingTemplate] = useState<PromptTemplate | null>(null);

    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const [deletingTemplate, setDeletingTemplate] = useState<PromptTemplate | null>(null);

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('prompt-templates.index'),
            {
                category: selectedCategory !== 'all' ? selectedCategory : undefined,
                search: searchQuery.trim() || undefined,
            },
            { preserveState: true, replace: true }
        );
    };

    const handleCategoryFilter = (cat: string) => {
        setSelectedCategory(cat);
        router.get(
            route('prompt-templates.index'),
            {
                category: cat !== 'all' ? cat : undefined,
                search: searchQuery.trim() || undefined,
            },
            { preserveState: true, replace: true }
        );
    };

    const handleToggleStatus = (template: PromptTemplate) => {
        router.patch(route('prompt-templates.toggle-status', template.id), {}, { preserveScroll: true });
    };

    const handleDuplicate = (template: PromptTemplate) => {
        router.post(route('prompt-templates.duplicate', template.id), {}, { preserveScroll: true });
    };

    const openCreateModal = () => {
        setEditingTemplate(null);
        setEditorModalOpen(true);
    };

    const openEditModal = (template: PromptTemplate) => {
        setEditingTemplate(template);
        setEditorModalOpen(true);
    };

    const openTestDrawer = (template: PromptTemplate) => {
        setTestingTemplate(template);
        setTestDrawerOpen(true);
    };

    const openDeleteModal = (template: PromptTemplate) => {
        setDeletingTemplate(template);
        setDeleteModalOpen(true);
    };

    const allCategories = ['all', ...Array.from(new Set([...PRESET_TEMPLATE_CATEGORIES, ...categories]))];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-center space-x-3">
                        <div className="p-2 bg-gradient-to-tr from-violet-600 to-indigo-500 rounded-xl shadow-lg shadow-indigo-500/20 text-white">
                            <Layers className="w-6 h-6" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                                Prompt Template Library
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold border border-indigo-200 dark:border-indigo-700/50">
                                    {templates.total} Templates
                                </span>
                            </h1>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                Manage and customize system instruction rules for AI Video Prompt Studio
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center space-x-3">
                        <Link
                            href={route('prompts.index')}
                            className="inline-flex items-center space-x-2 px-3.5 py-2 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold rounded-xl transition border border-gray-700"
                        >
                            <Wand2 className="w-4 h-4 text-violet-400" />
                            <span>Prompt Studio</span>
                        </Link>

                        <button
                            onClick={openCreateModal}
                            className="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/25 transition transform active:scale-95"
                        >
                            <Plus className="w-4 h-4" />
                            <span>Create Template</span>
                        </button>
                    </div>
                </div>
            }
        >
            <Head title="Prompt Template Library" />

            <div className="py-8 bg-gray-950 min-h-[calc(100vh-4rem)] text-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                    {/* FILTER & SEARCH BAR */}
                    <div className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-4 sm:p-6 shadow-2xl space-y-4">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            
                            {/* Category Filter Pills */}
                            <div className="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
                                {allCategories.map((cat) => {
                                    const isSelected = selectedCategory === cat;
                                    return (
                                        <button
                                            key={cat}
                                            onClick={() => handleCategoryFilter(cat)}
                                            className={`text-xs font-bold px-3 py-1.5 rounded-xl capitalize transition ${
                                                isSelected
                                                    ? 'bg-violet-600 text-white shadow-md shadow-violet-600/20'
                                                    : 'bg-gray-950/80 text-gray-400 hover:text-white border border-gray-800 hover:border-gray-700'
                                            }`}
                                        >
                                            {cat === 'all' ? 'All Categories' : cat}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Search Bar */}
                            <form onSubmit={handleSearchSubmit} className="relative w-full md:w-72">
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Search by template name or rules..."
                                    className="w-full bg-gray-950 border border-gray-800 rounded-xl pl-9 pr-4 py-2 text-xs text-gray-200 placeholder-gray-500 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition"
                                />
                                <Search className="w-4 h-4 text-gray-500 absolute left-3 top-2.5" />
                            </form>

                        </div>
                    </div>

                    {/* TEMPLATE CARDS GRID */}
                    {templates.data.length === 0 ? (
                        <div className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-12 text-center space-y-3">
                            <div className="w-12 h-12 mx-auto rounded-2xl bg-gray-800 flex items-center justify-center text-gray-500">
                                <Layers className="w-6 h-6" />
                            </div>
                            <h3 className="text-base font-bold text-white">No Prompt Templates Found</h3>
                            <p className="text-xs text-gray-400 max-w-sm mx-auto">
                                Create your first prompt template to enforce customized visual & structural AI prompt guidelines.
                            </p>
                            <button
                                onClick={openCreateModal}
                                className="inline-flex items-center space-x-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold rounded-xl transition"
                            >
                                <Plus className="w-4 h-4" />
                                <span>Create Template Now</span>
                            </button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {templates.data.map((tpl) => {
                                const isExpanded = expandedTemplateId === tpl.id;
                                return (
                                    <div
                                        key={tpl.id}
                                        className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-5 shadow-xl hover:border-gray-700 transition flex flex-col justify-between space-y-4 group"
                                    >
                                        <div className="space-y-3">
                                            {/* Header */}
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="space-y-1">
                                                    <h3 className="text-base font-bold text-white group-hover:text-violet-300 transition">
                                                        {tpl.name}
                                                    </h3>
                                                    <span className="inline-block text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded bg-violet-950 text-violet-300 border border-violet-800/50">
                                                        {tpl.category}
                                                    </span>
                                                </div>

                                                {/* Active Switch */}
                                                <button
                                                    onClick={() => handleToggleStatus(tpl)}
                                                    className={`px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border flex items-center gap-1 transition ${
                                                        tpl.is_active
                                                            ? 'bg-emerald-950/80 text-emerald-300 border-emerald-800/60'
                                                            : 'bg-gray-800 text-gray-400 border-gray-700'
                                                    }`}
                                                    title="Toggle template active status"
                                                >
                                                    {tpl.is_active ? <CheckCircle2 className="w-3 h-3 text-emerald-400" /> : <XCircle className="w-3 h-3 text-gray-500" />}
                                                    <span>{tpl.is_active ? 'Active' : 'Inactive'}</span>
                                                </button>
                                            </div>

                                            {/* Instruction Content */}
                                            <div className="p-3 bg-gray-950/80 rounded-xl border border-gray-800/80 space-y-2">
                                                <p className={`text-xs text-gray-300 font-mono leading-relaxed ${isExpanded ? '' : 'line-clamp-3'}`}>
                                                    {tpl.system_instruction}
                                                </p>
                                                {tpl.system_instruction.length > 120 && (
                                                    <button
                                                        onClick={() => setExpandedTemplateId(isExpanded ? null : tpl.id)}
                                                        className="text-[11px] font-bold text-violet-400 hover:text-violet-300 transition"
                                                    >
                                                        {isExpanded ? 'Show less' : 'Read full prompt instruction...'}
                                                    </button>
                                                )}
                                            </div>
                                        </div>

                                        {/* Footer Stats & Actions */}
                                        <div className="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                                            <span className="text-[11px] text-gray-500 flex items-center gap-1">
                                                <FileText className="w-3.5 h-3.5" />
                                                <span>Used: <strong className="text-gray-300">{tpl.content_prompts_count ?? 0}</strong> times</span>
                                            </span>

                                            <div className="flex items-center space-x-1">
                                                <button
                                                    onClick={() => openTestDrawer(tpl)}
                                                    className="p-2 text-emerald-400 hover:text-white hover:bg-emerald-950/60 rounded-lg border border-transparent hover:border-emerald-800/60 transition"
                                                    title="Test template in Live Playground"
                                                >
                                                    <FlaskConical className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleDuplicate(tpl)}
                                                    className="p-2 text-indigo-400 hover:text-white hover:bg-indigo-950/60 rounded-lg border border-transparent hover:border-indigo-800/60 transition"
                                                    title="Duplicate template"
                                                >
                                                    <Copy className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => openEditModal(tpl)}
                                                    className="p-2 text-violet-400 hover:text-white hover:bg-violet-950/60 rounded-lg border border-transparent hover:border-violet-800/60 transition"
                                                    title="Edit template"
                                                >
                                                    <Edit3 className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => openDeleteModal(tpl)}
                                                    className="p-2 text-rose-400 hover:text-white hover:bg-rose-950/60 rounded-lg border border-transparent hover:border-rose-800/60 transition"
                                                    title="Delete template"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {/* PAGINATION */}
                    {templates.last_page > 1 && (
                        <div className="flex items-center justify-center space-x-2 pt-4">
                            {templates.links.map((link, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true, replace: true })}
                                    disabled={!link.url}
                                    className={`px-3 py-1.5 rounded-xl text-xs font-bold transition ${
                                        link.active
                                            ? 'bg-violet-600 text-white shadow-md shadow-violet-600/20'
                                            : link.url
                                            ? 'bg-gray-900 border border-gray-800 text-gray-300 hover:bg-gray-800'
                                            : 'bg-gray-950 border border-gray-900 text-gray-600 cursor-not-allowed'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}

                </div>
            </div>

            {/* MODALS & DRAWERS */}
            <TemplateEditorModal
                isOpen={editorModalOpen}
                onClose={() => setEditorModalOpen(false)}
                template={editingTemplate}
                categories={categories}
            />

            <TemplateTestDrawer
                isOpen={testDrawerOpen}
                onClose={() => setTestDrawerOpen(false)}
                template={testingTemplate}
            />

            <DeleteConfirmModal
                isOpen={deleteModalOpen}
                onClose={() => setDeleteModalOpen(false)}
                template={deletingTemplate}
            />
        </AuthenticatedLayout>
    );
}
