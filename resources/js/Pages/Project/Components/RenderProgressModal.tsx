import React, { useEffect, useState } from 'react';
import axios from 'axios';
import {
    Loader2,
    Rocket,
    CheckCircle2,
    Film,
    AlertCircle,
    X,
} from 'lucide-react';

export interface RenderStatus {
    id: number;
    status: string;
    render_job_id?: string | null;
    video_url?: string | null;
    video_path?: string | null;
    render_error?: string | null;
    rendered_at?: string | null;
}

interface Props {
    isOpen: boolean;
    onClose: () => void;
    projectId: number;
    onProjectUpdated: (project: RenderStatus) => void;
}

const POLL_INTERVAL_MS = 3000;
const BUSY_STATUSES = ['processing', 'rendering'];

export default function RenderProgressModal({
    isOpen,
    onClose,
    projectId,
    onProjectUpdated,
}: Props) {
    const [project, setProject] = useState<RenderStatus | null>(null);
    const [fetchError, setFetchError] = useState<string | null>(null);

    useEffect(() => {
        if (!isOpen) return;

        let cancelled = false;
        let timer: ReturnType<typeof setTimeout>;

        const poll = async () => {
            try {
                const response = await axios.get(`/projects/${projectId}/render-status`);
                const data: RenderStatus = response.data.project;

                if (cancelled) return;

                setProject(data);
                setFetchError(null);

                if (!BUSY_STATUSES.includes(data.status)) {
                    onProjectUpdated(data);
                    return;
                }
            } catch (err: unknown) {
                if (cancelled) return;

                setProject(null);
                setFetchError(
                    axios.isAxiosError(err) && err.response?.data?.message
                        ? String(err.response.data.message)
                        : 'Không thể kết nối tới máy chủ khi kiểm tra trạng thái.'
                );
            }

            if (!cancelled) {
                timer = setTimeout(poll, POLL_INTERVAL_MS);
            }
        };

        timer = setTimeout(poll, 300);

        return () => {
            cancelled = true;
            clearTimeout(timer);
        };
    }, [isOpen, projectId, onProjectUpdated]);

    if (!isOpen) return null;

    const isBusy = project === null || BUSY_STATUSES.includes(project.status);
    const isCompleted = project?.status === 'completed';

    return (
        <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
            <div className="bg-gray-900 border border-gray-800 rounded-2xl max-w-md w-full p-6 text-center space-y-6 shadow-2xl relative overflow-hidden">
                {isBusy && (
                    <div className="relative w-20 h-20 mx-auto">
                        <div className="absolute inset-0 rounded-full border-4 border-violet-500/20 border-t-violet-500 animate-spin" />
                        <div className="absolute inset-2 rounded-full border-4 border-indigo-500/20 border-b-indigo-500 animate-spin" style={{ animationDirection: 'reverse', animationDuration: '1.5s' }} />
                        <div className="absolute inset-0 flex items-center justify-center text-violet-400">
                            <Rocket className="w-8 h-8 animate-bounce text-indigo-400" />
                        </div>
                    </div>
                )}

                {isCompleted && (
                    <CheckCircle2 className="w-16 h-16 mx-auto text-emerald-400" />
                )}

                {project?.status === 'failed' && (
                    <AlertCircle className="w-16 h-16 mx-auto text-rose-400" />
                )}

                <div className="space-y-2">
                    {isBusy && (
                        <>
                            <h3 className="text-lg font-bold text-white">Đang Render Video Hoàn Chỉnh...</h3>
                            <p className="text-xs text-gray-400">
                                Hệ thống đang sinh asset và gửi payload tới Creatomate Engine. Vui lòng chờ...
                            </p>
                        </>
                    )}
                    {isCompleted && (
                        <>
                            <h3 className="text-lg font-bold text-emerald-400">Render Hoàn Tất!</h3>
                            <p className="text-xs text-gray-400">
                                Video thành phẩm đã sẵn sàng. Bạn có thể xem và tải xuống bên dưới.
                            </p>
                        </>
                    )}
                    {project?.status === 'failed' && (
                        <>
                            <h3 className="text-lg font-bold text-rose-400">Render Thất Bại</h3>
                            <p className="text-xs text-gray-400">
                                {project.render_error || 'Đã có lỗi xảy ra trong quá trình render. Vui lòng thử lại.'}
                            </p>
                        </>
                    )}
                    {fetchError && isBusy && (
                        <p className="text-xs text-amber-400">{fetchError}</p>
                    )}
                </div>

                {isCompleted && project?.video_url && (
                    <div className="space-y-2">
                        <video
                            src={project.video_url}
                            controls
                            className="w-full rounded-xl border border-gray-700 bg-black"
                        />
                        <a
                            href={project.video_url}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center justify-center w-full gap-2 px-4 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-xl text-xs font-bold transition"
                        >
                            <Film className="w-4 h-4" />
                            Mở Video trong tab mới
                        </a>
                    </div>
                )}

                {project?.render_job_id && (
                    <div className="p-3 bg-gray-950 rounded-xl border border-gray-800 text-xs text-gray-400 font-mono">
                        <span>Job ID: <strong className="text-indigo-300">{project.render_job_id}</strong></span>
                    </div>
                )}

                <div className="flex items-center space-x-2">
                    <button
                        onClick={onClose}
                        className="w-full py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold rounded-xl transition flex items-center justify-center space-x-2"
                    >
                        <X className="w-3.5 h-3.5" />
                        <span>{isBusy ? 'Đóng cửa sổ theo dõi' : 'Đóng'}</span>
                    </button>
                    {isBusy && (
                        <Loader2 className="w-4 h-4 animate-spin text-indigo-400 shrink-0" />
                    )}
                </div>
            </div>
        </div>
    );
}