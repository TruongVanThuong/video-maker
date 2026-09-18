import React from 'react';
import { Loader2, Rocket, CheckCircle2, Film } from 'lucide-react';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    renderJobId?: string | null;
}

export default function RenderProgressModal({ isOpen, onClose, renderJobId }: Props) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
            <div className="bg-gray-900 border border-gray-800 rounded-2xl max-w-md w-full p-6 text-center space-y-6 shadow-2xl relative overflow-hidden">
                
                <div className="relative w-20 h-20 mx-auto">
                    <div className="absolute inset-0 rounded-full border-4 border-violet-500/20 border-t-violet-500 animate-spin" />
                    <div className="absolute inset-2 rounded-full border-4 border-indigo-500/20 border-b-indigo-500 animate-spin" style={{ animationDirection: 'reverse', animationDuration: '1.5s' }} />
                    <div className="absolute inset-0 flex items-center justify-center text-violet-400">
                        <Rocket className="w-8 h-8 animate-bounce text-indigo-400" />
                    </div>
                </div>

                <div className="space-y-2">
                    <h3 className="text-lg font-bold text-white">Đang Render Video Hoàn Chỉnh...</h3>
                    <p className="text-xs text-gray-400">
                        Hệ thống đang ghép các phân cảnh, voiceover audio và hiệu ứng chuyển cảnh tự động.
                    </p>
                </div>

                {renderJobId && (
                    <div className="p-3 bg-gray-950 rounded-xl border border-gray-800 text-xs text-gray-400 font-mono">
                        <span>Job ID: <strong className="text-indigo-300">{renderJobId}</strong></span>
                    </div>
                )}

                <div className="space-y-2 text-left text-xs bg-gray-950 p-3.5 rounded-xl border border-gray-800">
                    <div className="flex items-center space-x-2 text-emerald-400">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        <span>Kiểm tra & tổng hợp voiceover audio</span>
                    </div>
                    <div className="flex items-center space-x-2 text-emerald-400">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        <span>Kiểm tra & tổng hợp hình ảnh AI</span>
                    </div>
                    <div className="flex items-center space-x-2 text-indigo-400 animate-pulse">
                        <Film className="w-3.5 h-3.5" />
                        <span>Gửi Render Payload sang Creatomate Engine</span>
                    </div>
                </div>

                <button
                    onClick={onClose}
                    className="w-full py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold rounded-xl transition"
                >
                    Đóng cửa sổ theo dõi
                </button>
            </div>
        </div>
    );
}
