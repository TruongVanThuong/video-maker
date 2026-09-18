import React, { useState } from 'react';
import { Head, usePage, Link } from '@inertiajs/react';
import axios from 'axios';
import {
    Rocket,
    Film,
    Wand2,
    CheckCircle2,
    AlertCircle,
    Volume2,
    Image as ImageIcon,
    Clock,
    Layers,
    ArrowLeft,
    Sparkles,
    Play,
    Loader2
} from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SceneCard, { Scene, VoiceProfile } from './Components/SceneCard';
import RenderProgressModal from './Components/RenderProgressModal';

interface Project {
    id: number;
    title: string;
    source_type: string;
    raw_input: string;
    status: string;
    batch_id?: string | null;
    render_job_id?: string | null;
    scenes: Scene[];
    created_at?: string;
}

interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    project: Project;
    voiceProfiles: VoiceProfile[];
    [key: string]: unknown;
}

export default function ProjectStudio() {
    const { auth, project: initialProject, voiceProfiles = [] } = usePage<PageProps>().props;

    const [project, setProject] = useState<Project>(initialProject);
    const [scenes, setScenes] = useState<Scene[]>(initialProject.scenes || []);

    const [isRendering, setIsRendering] = useState(false);
    const [showRenderModal, setShowRenderModal] = useState(false);
    const [notification, setNotification] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    const handleSceneUpdated = (updatedScene: Scene) => {
        setScenes((prev) =>
            prev.map((s) => (s.id === updatedScene.id ? updatedScene : s))
        );
    };

    const handleStartRenderBatch = async () => {
        setIsRendering(true);
        setNotification(null);
        try {
            const response = await axios.post(`/projects/${project.id}/render`);
            if (response.data.success) {
                setProject(response.data.project);
                setScenes(response.data.project.scenes || []);
                setShowRenderModal(true);
            } else {
                setNotification({ type: 'error', text: response.data.message });
            }
        } catch (err: unknown) {
            setNotification({ type: 'error', text: 'Không thể khởi chạy Batch Render Video.' });
        } finally {
            setIsRendering(false);
        }
    };

    // Calculate Summary Stats
    const totalDuration = scenes.reduce((acc, s) => acc + (s.duration || 0), 0);
    const voiceReadyCount = scenes.filter((s) => !!s.audio_path).length;
    const imageReadyCount = scenes.filter((s) => !!s.image_path).length;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center space-x-3">
                        <Link
                            href={route('prompts.index')}
                            className="p-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl transition border border-gray-700"
                            title="Quay lại Prompt Studio"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </Link>
                        <div>
                            <div className="flex items-center space-x-2">
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                    {project.title}
                                </h1>
                                <span className={`text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider border ${
                                    project.status === 'rendering'
                                        ? 'bg-indigo-950 text-indigo-300 border-indigo-700/50 animate-pulse'
                                        : project.status === 'completed'
                                        ? 'bg-emerald-950 text-emerald-300 border-emerald-700/50'
                                        : 'bg-gray-800 text-gray-300 border-gray-700'
                                }`}>
                                    {project.status}
                                </span>
                            </div>
                            <p className="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <span>Project Studio Workspace</span>
                                <span>•</span>
                                <span>Nguồn: {project.source_type.toUpperCase()}</span>
                            </p>
                        </div>
                    </div>

                    {/* Primary CTA Render Button */}
                    <button
                        type="button"
                        onClick={handleStartRenderBatch}
                        disabled={isRendering || scenes.length === 0}
                        className="px-6 py-3.5 bg-gradient-to-r from-violet-600 via-indigo-600 to-emerald-600 hover:from-violet-500 hover:to-emerald-500 text-white font-extrabold text-sm rounded-xl shadow-xl shadow-indigo-600/30 hover:shadow-indigo-500/50 disabled:opacity-50 transition transform active:scale-95 flex items-center justify-center space-x-2 shrink-0"
                    >
                        {isRendering ? (
                            <>
                                <Loader2 className="w-5 h-5 animate-spin" />
                                <span>Đang khởi chạy Batch Render...</span>
                            </>
                        ) : (
                            <>
                                <Rocket className="w-5 h-5 text-amber-300" />
                                <span>🚀 Bắt đầu Render Video Hoàn Chỉnh</span>
                            </>
                        )}
                    </button>
                </div>
            }
        >
            <Head title={`Studio - ${project.title}`} />

            <div className="py-8 bg-gray-950 min-h-[calc(100vh-4rem)] text-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                    {/* TIMELINE SUMMARY DASHBOARD BAR */}
                    <div className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-6 shadow-2xl grid grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <div className="p-4 bg-gray-950/60 rounded-xl border border-gray-800/80 flex items-center space-x-3">
                            <div className="p-3 bg-violet-600/20 text-violet-400 rounded-xl">
                                <Layers className="w-5 h-5" />
                            </div>
                            <div>
                                <span className="text-xs font-semibold text-gray-400 uppercase">Tổng Phân Cảnh</span>
                                <p className="text-xl font-extrabold text-white">{scenes.length} Scenes</p>
                            </div>
                        </div>

                        <div className="p-4 bg-gray-950/60 rounded-xl border border-gray-800/80 flex items-center space-x-3">
                            <div className="p-3 bg-amber-600/20 text-amber-400 rounded-xl">
                                <Clock className="w-5 h-5" />
                            </div>
                            <div>
                                <span className="text-xs font-semibold text-gray-400 uppercase">Thời Lượng Dự Kiến</span>
                                <p className="text-xl font-extrabold text-white">{totalDuration.toFixed(1)} Giây</p>
                            </div>
                        </div>

                        <div className="p-4 bg-gray-950/60 rounded-xl border border-gray-800/80 flex items-center space-x-3">
                            <div className="p-3 bg-emerald-600/20 text-emerald-400 rounded-xl">
                                <Volume2 className="w-5 h-5" />
                            </div>
                            <div>
                                <span className="text-xs font-semibold text-gray-400 uppercase">Voice Ready</span>
                                <p className="text-xl font-extrabold text-white">{voiceReadyCount} / {scenes.length}</p>
                            </div>
                        </div>

                        <div className="p-4 bg-gray-950/60 rounded-xl border border-gray-800/80 flex items-center space-x-3">
                            <div className="p-3 bg-indigo-600/20 text-indigo-400 rounded-xl">
                                <ImageIcon className="w-5 h-5" />
                            </div>
                            <div>
                                <span className="text-xs font-semibold text-gray-400 uppercase">Ảnh AI Ready</span>
                                <p className="text-xl font-extrabold text-white">{imageReadyCount} / {scenes.length}</p>
                            </div>
                        </div>

                    </div>

                    {/* NOTIFICATION ALERT */}
                    {notification && (
                        <div className={`p-4 rounded-xl text-sm font-bold flex items-center gap-2 ${
                            notification.type === 'success'
                                ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/60'
                                : 'bg-rose-950/80 text-rose-300 border border-rose-800/60'
                        }`}>
                            {notification.type === 'success' ? <CheckCircle2 className="w-5 h-5" /> : <AlertCircle className="w-5 h-5" />}
                            <span>{notification.text}</span>
                        </div>
                    )}

                    {/* SCENES CARDS LIST */}
                    <div className="space-y-6">
                        <div className="flex items-center justify-between border-b border-gray-800 pb-3">
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <Film className="w-5 h-5 text-emerald-400" />
                                Danh Sách Phân Cảnh (Scenes Timeline)
                            </h2>
                            <span className="text-xs text-gray-400">
                                Sắp xếp theo thứ tự `scene_index`
                            </span>
                        </div>

                        {scenes.length === 0 ? (
                            <div className="bg-gray-900/90 border border-gray-800 rounded-2xl p-12 text-center text-gray-400">
                                Chưa có phân cảnh nào được tạo cho dự án này.
                            </div>
                        ) : (
                            scenes.map((scene) => (
                                <SceneCard
                                    key={scene.id}
                                    scene={scene}
                                    voiceProfiles={voiceProfiles}
                                    onSceneUpdated={handleSceneUpdated}
                                />
                            ))
                        )}
                    </div>

                    {/* BOTTOM RENDER CTA BANNER */}
                    <div className="bg-gradient-to-r from-violet-950/60 via-indigo-950/60 to-gray-900 border border-violet-900/50 rounded-2xl p-6 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 className="text-lg font-bold text-white flex items-center gap-2">
                                <Sparkles className="w-5 h-5 text-amber-400" />
                                Đã hoàn tất chỉnh sửa phân cảnh?
                            </h3>
                            <p className="text-xs text-gray-300 mt-1">
                                Nhấp bên dưới để chạy Batch Job ghép toàn bộ audio, hình ảnh và render video hoàn chỉnh.
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={handleStartRenderBatch}
                            disabled={isRendering || scenes.length === 0}
                            className="px-6 py-3 bg-gradient-to-r from-violet-600 via-indigo-600 to-emerald-600 hover:from-violet-500 hover:to-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center justify-center space-x-2 shrink-0"
                        >
                            <Rocket className="w-4 h-4 text-amber-300" />
                            <span>🚀 Bắt đầu Render Video Hoàn Chỉnh</span>
                        </button>
                    </div>

                </div>
            </div>

            {/* RENDER PROGRESS MODAL */}
            <RenderProgressModal
                isOpen={showRenderModal}
                onClose={() => setShowRenderModal(false)}
                renderJobId={project.render_job_id}
            />
        </AuthenticatedLayout>
    );
}
