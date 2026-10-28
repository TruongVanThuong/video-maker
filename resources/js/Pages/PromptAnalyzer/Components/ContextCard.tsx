import { useState } from 'react';
import { Pencil, Check, X as XIcon } from 'lucide-react';

interface ContextCardProps {
    title: string;
    value?: string | null;
    icon: string;
    color: string;
    fullWidth?: boolean;
    onSave?: (newValue: string) => void; // truyền vào -> card cho phép edit
    saving?: boolean;
}

export default function ContextCard({
    title,
    value,
    icon,
    color,
    fullWidth = false,
    onSave,
    saving = false,
}: ContextCardProps) {
    const [isEditing, setIsEditing] = useState(false);
    const [draft, setDraft] = useState(value ?? '');

    const startEdit = () => {
        setDraft(value ?? '');
        setIsEditing(true);
    };

    const cancelEdit = () => {
        setIsEditing(false);
        setDraft(value ?? '');
    };

    const confirmSave = () => {
        if (!onSave || draft.trim() === (value ?? '')) {
            setIsEditing(false);
            return;
        }
        onSave(draft.trim());
        setIsEditing(false);
    };

    return (
        <div
            className={`p-3 bg-gray-950/80 rounded-xl border rounded-xl space-y-1 transition ${
                isEditing ? 'border-violet-600' : 'border-gray-800'
            } ${fullWidth ? 'sm:col-span-2' : ''}`}
        >
            <div className="flex items-center justify-between">
                <span className="text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>{icon}</span>
                    {title}
                </span>

                {onSave && !isEditing && (
                    <button
                        type="button"
                        onClick={startEdit}
                        className="text-gray-500 hover:text-violet-400 transition"
                        title={`Sửa ${title}`}
                    >
                        <Pencil className="w-3.5 h-3.5" />
                    </button>
                )}
            </div>

            {isEditing ? (
                <div className="space-y-2">
                    <textarea
                        autoFocus
                        rows={3}
                        value={draft}
                        onChange={(e) => setDraft(e.target.value)}
                        disabled={saving}
                        className="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-y"
                    />
                    <div className="flex items-center justify-end gap-1.5">
                        <button
                            type="button"
                            onClick={cancelEdit}
                            disabled={saving}
                            className="p-1.5 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition"
                        >
                            <XIcon className="w-3.5 h-3.5" />
                        </button>
                        <button
                            type="button"
                            onClick={confirmSave}
                            disabled={saving}
                            className="p-1.5 text-emerald-400 hover:text-white rounded-lg hover:bg-emerald-900/60 transition"
                        >
                            <Check className="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            ) : (
                <p className={`text-xs font-semibold ${color} leading-relaxed`}>{value || 'N/A'}</p>
            )}
        </div>
    );
}