import { useState } from "react";
import { Icon } from "../Common/Icon";
import { useClipboard } from "../../../Hooks/BestDeals/useClipboard";

interface DealHunterProps {
    onHunt: (value: string) => void;
}

export function DealHunter({ onHunt }: DealHunterProps) {
    const [value, setValue] = useState("");

    const { paste } = useClipboard();

    const handlePaste = async () => {
        const text = await paste();

        if (text) {
            setValue(text);
        }
    };

    return (
        <section className="mx-margin-mobile">
            <div className="relative overflow-hidden rounded-xl bg-gradient-to-br from-surface-container-lowest via-surface-container-low to-surface-container p-4 shadow-md">
                <div className="absolute -top-12 -right-12 w-36 h-36 bg-primary-fixed-dim/40 rounded-full blur-2xl" />

                <div className="absolute -bottom-8 -left-8 w-28 h-28 bg-secondary-fixed/50 rounded-full blur-xl" />

                <div className="relative z-10 flex flex-col space-y-3">
                    <div className="flex flex-col bg-surface-container-lowest rounded-xl p-2.5 shadow-sm space-y-2">
                        <div className="flex items-center gap-2">
                            <Icon
                                name="link"
                                className="text-[20px] text-outline"
                            />

                            <input
                                value={value}
                                onChange={(e) => setValue(e.target.value)}
                                className="w-full bg-transparent text-on-surface placeholder:text-outline focus:outline-none"
                                placeholder="Dán link Shopee / Lazada / TikTok Shop..."
                            />

                            <button
                                onClick={handlePaste}
                                className="px-2 py-1 rounded-lg bg-surface-container text-on-surface-variant flex items-center gap-1"
                            >
                                <Icon
                                    name="content_paste"
                                    className="text-[16px]"
                                />
                                Dán
                            </button>
                        </div>

                        <button
                            onClick={() => onHunt(value)}
                            className="w-full h-11 rounded-full bg-gradient-to-r from-secondary-container via-secondary-fixed-dim to-secondary-container text-on-secondary-fixed font-label-lg font-bold flex items-center justify-center gap-2 shadow-md"
                        >
                            <Icon
                                name="offline_bolt"
                                className="text-[20px]"
                                filled
                            />
                            Săn Deal & Gom Mã Ngay
                        </button>
                    </div>

                </div>
            </div>
        </section>
    );
}
