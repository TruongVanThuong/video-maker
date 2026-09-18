import React, { useState } from 'react';
import axios from 'axios';
import {
    Mic,
    Image as ImageIcon,
    Save,
    Play,
    Loader2,
    Volume2,
    Video,
    Sparkles,
    CheckCircle2,
    AlertCircle,
    Eye,
    X,
    Clock,
    Camera
} from 'lucide-react';

export interface CameraRule {
    motion?: string;
    speed?: string;
    zoom?: string;
    [key: string]: unknown;
}

export interface Scene {
    id: number;
    project_id: number;
    scene_index: number;
    dialogue: string;
    voice_id: string | null;
    image_prompt: string | null;
    camera_rule: CameraRule | null;
    audio_path: string | null;
    image_path: string | null;
    duration: number;
    created_at?: string;
    updated_at?: string;
}

export interface VoiceProfile {
    id: string;
    name: string;
}

interface SceneCardProps {
    scene: Scene;
    voiceProfiles: VoiceProfile[];
    onSceneUpdated: (updatedScene: Scene) => void;
}

const CAMERA_MOTIONS = [
    { id: 'pan_right', name: 'Pan Right →' },
    { id: 'pan_left', name: 'Pan Left ←' },
    { id: 'zoom_in', name: 'Zoom In 🔍' },
    { id: 'zoom_out', name: 'Zoom Out 🔎' },
    { id: 'tilt_up', name: 'Tilt Up ⬆️' },
    { id: 'tilt_down', name: 'Tilt Down ⬇️' },
    { id: 'orbit_360', name: 'Orbit 360° 🔄' },
    { id: 'static', name: 'Static Frame ⏹️' },
];

export default function SceneCard({ scene, voiceProfiles, onSceneUpdated }: SceneCardProps) {
    const [dialogue, setDialogue] = useState(scene.dialogue || '');
    const [voiceId, setVoiceId] = useState(scene.voice_id || '21m00Tcm4TlvDq8ikWAM');
    const [imagePrompt, setImagePrompt] = useState(scene.image_prompt || '');
    const [cameraMotion, setCameraMotion] = useState(
        scene.camera_rule?.motion || 'pan_right'
    );

    // Loading States
    const [isSaving, setIsSaving] = useState(false);
    const [isGeneratingVoice, setIsGeneratingVoice] = useState(false);
    const [isGeneratingImage, setIsGeneratingImage] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const [showImageLightbox, setShowImageLightbox] = useState(false);

    const handleSave = async () => {
        setIsSaving(true);
        setMessage(null);
        try {
            const response = await axios.patch(`/scenes/${scene.id}`, {
                dialogue,
                voice_id: voiceId,
                image_prompt: imagePrompt,
                camera_rule: {
                    ...scene.camera_rule,
                    motion: cameraMotion,
                    speed: 'normal',
                },
            });
            onSceneUpdated(response.data.scene);
            setMessage({ type: 'success', text: 'Đã lưu thông tin phân cảnh.' });
            setTimeout(() => setMessage(null), 2500);
        } catch (err: unknown) {
            setMessage({ type: 'error', text: 'Lỗi khi lưu thay đổi phân cảnh.' });
        } finally {
            setIsSaving(false);
        }
    };

    const handleGenerateVoiceTest = async () => {
        setIsGeneratingVoice(true);
        setMessage(null);
        try {
            const response = await axios.post(`/scenes/${scene.id}/generate-voice`);
            console.log(response.data);
            if (response.data.success) {
                onSceneUpdated(response.data.scene);
                setMessage({ type: 'success', text: 'Đã sinh Voice thử thành công!' });
            } else {
                setMessage({ type: 'error', text: response.data.message });
            }
        } catch (err: unknown) {
            setMessage({ type: 'error', text: 'Lỗi kết nối khi sinh Voice.' });
        } finally {
            setIsGeneratingVoice(false);
        }
    };

    const handleGenerateImageTest = async () => {
        setIsGeneratingImage(true);
        setMessage(null);
        try {
            const response = await axios.post(`/scenes/${scene.id}/generate-image`);
            if (response.data.success) {
                onSceneUpdated(response.data.scene);
                setMessage({ type: 'success', text: 'Đã sinh Ảnh thử thành công!' });
            } else {
                setMessage({ type: 'error', text: response.data.message });
            }
        } catch (err: unknown) {
            setMessage({ type: 'error', text: 'Lỗi kết nối khi sinh Ảnh.' });
        } finally {
            setIsGeneratingImage(false);
        }
    };

    return (
        <div className="bg-gray-900/90 backdrop-blur-xl border border-gray-800 rounded-2xl p-6 shadow-xl space-y-6 hover:border-gray-700 transition">
            
            {/* Header: Scene Index & Assets Status */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-800/80 pb-4">
                <div className="flex items-center space-x-3">
                    <span className="w-9 h-9 rounded-xl bg-violet-600/20 text-violet-400 font-extrabold text-sm flex items-center justify-center border border-violet-500/30 shadow-inner">
                        #{scene.scene_index}
                    </span>
                    <div>
                        <h3 className="text-base font-bold text-white flex items-center gap-2">
                            Phân Cảnh {scene.scene_index}
                        </h3>
                        <span className="text-xs text-gray-400 flex items-center gap-1.5">
                            <Clock className="w-3.5 h-3.5 text-amber-400" />
                            Thời lượng: <strong className="text-gray-200">{scene.duration || 0}s</strong>
                        </span>
                    </div>
                </div>

                {/* Status Badges */}
                <div className="flex items-center space-x-2">
                    <span className={`text-[11px] font-bold px-2.5 py-1 rounded-lg border flex items-center gap-1 ${
                        scene.audio_path
                            ? 'bg-emerald-950/80 text-emerald-300 border-emerald-800/60'
                            : 'bg-amber-950/80 text-amber-300 border-amber-800/60'
                    }`}>
                        <Volume2 className="w-3.5 h-3.5" />
                        {scene.audio_path ? 'Voice Sẵn sàng' : 'Voice Chưa sinh'}
                    </span>

                    <span className={`text-[11px] font-bold px-2.5 py-1 rounded-lg border flex items-center gap-1 ${
                        scene.image_path
                            ? 'bg-emerald-950/80 text-emerald-300 border-emerald-800/60'
                            : 'bg-amber-950/80 text-amber-300 border-amber-800/60'
                    }`}>
                        <ImageIcon className="w-3.5 h-3.5" />
                        {scene.image_path ? 'Ảnh Sẵn sàng' : 'Ảnh Chưa sinh'}
                    </span>
                </div>
            </div>

            {/* Editable Controls Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {/* LEFT: Dialogue & Voice Controls */}
                <div className="space-y-4 bg-gray-950/50 p-4 rounded-xl border border-gray-800/80">
                    <div className="flex items-center justify-between">
                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <Mic className="w-4 h-4 text-violet-400" />
                            Lời thoại / Giọng đọc (Dialogue)
                        </label>
                    </div>

                    <textarea
                        rows={3}
                        value={dialogue}
                        onChange={(e) => setDialogue(e.target.value)}
                        placeholder="Nhập câu thoại hoặc lời dẫn câu chuyện..."
                        className="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition"
                    />

                    {/* Voice Profile Dropdown */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold text-gray-400">
                            Hồ sơ Giọng Đọc (ElevenLabs Voice Profile)
                        </label>
                        <select
                            value={voiceId}
                            onChange={(e) => setVoiceId(e.target.value)}
                            className="w-full bg-gray-900 border border-gray-800 rounded-xl px-3 py-2 text-xs text-gray-200 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition cursor-pointer"
                        >
                            {voiceProfiles.map((v) => (
                                <option key={v.id} value={v.id}>
                                    {v.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Voice Action & Audio Player */}
                    <div className="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-gray-800/60">
                        <button
                            type="button"
                            onClick={handleGenerateVoiceTest}
                            disabled={isGeneratingVoice || !dialogue.trim()}
                            className="px-3.5 py-2 bg-violet-600/20 hover:bg-violet-600/30 text-violet-300 border border-violet-500/40 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 disabled:opacity-50"
                        >
                            {isGeneratingVoice ? (
                                <Loader2 className="w-4 h-4 animate-spin text-violet-400" />
                            ) : (
                                <Play className="w-4 h-4 fill-violet-300" />
                            )}
                            <span>{isGeneratingVoice ? 'Đang sinh Voice...' : 'Sinh Voice thử'}</span>
                        </button>

                        {scene.audio_path && (
                            <audio
                                src={scene.audio_path}
                                controls
                                className="h-8 w-full sm:w-48 text-xs accent-violet-500"
                            />
                        )}
                    </div>
                </div>

                {/* RIGHT: Image Prompt & Camera Controls */}
                <div className="space-y-4 bg-gray-950/50 p-4 rounded-xl border border-gray-800/80">
                    <div className="flex items-center justify-between">
                        <label className="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <ImageIcon className="w-4 h-4 text-emerald-400" />
                            Câu lệnh sinh ảnh (Image Prompt)
                        </label>
                    </div>

                    <textarea
                        rows={3}
                        value={imagePrompt}
                        onChange={(e) => setImagePrompt(e.target.value)}
                        placeholder="Nhập prompt miêu tả cảnh quay chi tiết..."
                        className="w-full bg-gray-900 border border-gray-800 rounded-xl p-3 text-sm text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition font-mono"
                    />

                    {/* Camera Rule Dropdown */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold text-gray-400 flex items-center gap-1">
                            <Camera className="w-3.5 h-3.5 text-indigo-400" />
                            Góc & Chuyển Động Máy Quay (Camera Motion Rule)
                        </label>
                        <select
                            value={cameraMotion}
                            onChange={(e) => setCameraMotion(e.target.value)}
                            className="w-full bg-gray-900 border border-gray-800 rounded-xl px-3 py-2 text-xs text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition cursor-pointer"
                        >
                            {CAMERA_MOTIONS.map((m) => (
                                <option key={m.id} value={m.id}>
                                    {m.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Image Action & Preview Thumbnail */}
                    <div className="pt-2 flex items-center justify-between gap-3 border-t border-gray-800/60">
                        <button
                            type="button"
                            onClick={handleGenerateImageTest}
                            disabled={isGeneratingImage || !imagePrompt.trim()}
                            className="px-3.5 py-2 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 disabled:opacity-50"
                        >
                            {isGeneratingImage ? (
                                <Loader2 className="w-4 h-4 animate-spin text-emerald-400" />
                            ) : (
                                <Sparkles className="w-4 h-4" />
                            )}
                            <span>{isGeneratingImage ? 'Đang sinh Ảnh...' : 'Sinh Ảnh thử'}</span>
                        </button>

                        {scene.image_path && (
                            <div className="relative group">
                                <img
                                    src={scene.image_path}
                                    alt={`Scene #${scene.scene_index}`}
                                    className="w-16 h-10 object-cover rounded-lg border border-gray-700 shadow-sm cursor-pointer hover:opacity-90 transition"
                                    onClick={() => setShowImageLightbox(true)}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowImageLightbox(true)}
                                    className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white"
                                >
                                    <Eye className="w-4 h-4" />
                                </button>
                            </div>
                        )}
                    </div>
                </div>

            </div>

            {/* Notification message */}
            {message && (
                <div className={`p-3 rounded-xl text-xs font-semibold flex items-center gap-2 ${
                    message.type === 'success'
                        ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/60'
                        : 'bg-rose-950/80 text-rose-300 border border-rose-800/60'
                }`}>
                    {message.type === 'success' ? <CheckCircle2 className="w-4 h-4" /> : <AlertCircle className="w-4 h-4" />}
                    <span>{message.text}</span>
                </div>
            )}

            {/* Save Card Action */}
            <div className="flex justify-end pt-2 border-t border-gray-800/80">
                <button
                    type="button"
                    onClick={handleSave}
                    disabled={isSaving}
                    className="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition flex items-center space-x-1.5 shadow-sm"
                >
                    {isSaving ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Save className="w-3.5 h-3.5 text-indigo-400" />}
                    <span>Lưu Cấu Hình Phân Cảnh</span>
                </button>
            </div>

            {/* LIGHTBOX MODAL FOR IMAGE PREVIEW */}
            {showImageLightbox && scene.image_path && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
                    <div className="relative max-w-3xl w-full bg-gray-900 border border-gray-800 rounded-2xl p-4 overflow-hidden shadow-2xl">
                        <button
                            onClick={() => setShowImageLightbox(false)}
                            className="absolute top-4 right-4 p-2 bg-gray-800 text-gray-300 hover:text-white rounded-xl transition z-10"
                        >
                            <X className="w-5 h-5" />
                        </button>
                        <h4 className="text-sm font-bold text-white mb-3">
                            Xem Trước Ảnh Phân Cảnh #{scene.scene_index}
                        </h4>
                        <img
                            src={scene.image_path}
                            alt={`Preview Scene #${scene.scene_index}`}
                            className="w-full max-h-[70vh] object-contain rounded-xl border border-gray-800"
                        />
                        <p className="text-xs text-gray-400 font-mono mt-3 p-2 bg-gray-950 rounded-lg">
                            {scene.image_prompt}
                        </p>
                    </div>
                </div>
            )}

        </div>
    );
}
