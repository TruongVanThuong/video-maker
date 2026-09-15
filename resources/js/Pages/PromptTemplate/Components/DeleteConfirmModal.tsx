import React from 'react';
import { useForm } from '@inertiajs/react';
import { AlertTriangle, X, Trash2 } from 'lucide-react';
import { PromptTemplate } from '@/types/promptTemplate';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    template: PromptTemplate | null;
}

export default function DeleteConfirmModal({ isOpen, onClose, template }: Props) {
    const { delete: destroy, processing } = useForm();

    if (!isOpen || !template) return null;

    const handleDelete = () => {
        destroy(route('prompt-templates.destroy', template.id), {
            onSuccess: () => {
                onClose();
            },
        });
    };

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-md flex items-center justify-center p-4">
            <div className="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-5 text-center">
                
                <div className="w-12 h-12 mx-auto rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center">
                    <AlertTriangle className="w-6 h-6" />
                </div>

                <div className="space-y-1.5">
                    <h3 className="text-lg font-bold text-white">Delete Prompt Template?</h3>
                    <p className="text-xs text-gray-400">
                        Are you sure you want to delete <strong className="text-gray-200">{template.name}</strong>? This action cannot be undone.
                    </p>
                </div>

                <div className="flex items-center justify-center space-x-3 pt-3">
                    <button
                        type="button"
                        onClick={onClose}
                        className="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold rounded-xl transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onClick={handleDelete}
                        disabled={processing}
                        className="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 transition flex items-center space-x-1.5"
                    >
                        <Trash2 className="w-4 h-4" />
                        <span>Delete Template</span>
                    </button>
                </div>

            </div>
        </div>
    );
}
