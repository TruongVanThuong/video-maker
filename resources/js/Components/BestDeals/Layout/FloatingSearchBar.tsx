import { Icon } from "../Common/Icon";

interface Props {
    onClick: () => void;
}

export function FloatingSearchBar({ onClick }: Props) {
    return (
        <div className="fixed bottom-20 inset-x-0 z-40 px-margin-mobile pointer-events-none">
            <div className="max-w-md mx-auto pointer-events-auto bg-surface-container-lowest/95 backdrop-blur-xl rounded-full p-1.5 shadow-[0_4px_20px_rgba(15,23,42,0.12)] flex items-center justify-between gap-space-sm">
                <div className="flex items-center gap-space-xs pl-space-sm overflow-hidden">
                    <Icon
                        name="link"
                        className="text-[20px] text-primary-container"
                    />

                    <span className="text-xs truncate text-on-surface-variant">
                        Dán link Shopee, TikTok, Lazada...
                    </span>
                </div>

                <button
                    onClick={onClick}
                    className="h-10 px-space-md bg-secondary-container hover:bg-secondary-fixed text-on-secondary-fixed rounded-full flex items-center gap-1 text-xs font-semibold shadow-sm shrink-0"
                >
                    <Icon name="content_paste_go" className="text-[18px]" />
                    Săn Ngay
                </button>
            </div>
        </div>
    );
}
