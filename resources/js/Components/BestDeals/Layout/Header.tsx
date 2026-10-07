import { Icon } from "../Common/Icon";

export function Header() {
    return (
        <header className="fixed top-0 z-50 w-full pt-safe bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
            <div className="h-16 px-margin-mobile flex items-center justify-between">
                <div className="flex items-center gap-space-sm">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-primary-container flex items-center justify-center">
                            <Icon
                                name="bolt"
                                className="text-white text-[20px]"
                                filled
                            />
                        </div>

                        <span className="font-headline-sm text-headline-sm text-primary tracking-tight">
                            VoucherHunt
                        </span>

                        <span className="px-space-xs py-0.5 rounded-full bg-secondary-container text-on-secondary-fixed font-label-sm text-label-sm uppercase">
                            LIVE
                        </span>
                    </div>
                </div>

                <div className="flex items-center gap-space-xs">
                    <button
                        aria-label="Thông báo"
                        className="relative w-11 h-11 flex items-center justify-center rounded-full text-on-surface hover:bg-surface-container"
                    >
                        <Icon name="notifications" className="text-[24px]" />

                        <span className="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-error text-on-error font-label-sm rounded-full flex items-center justify-center ring-2 ring-surface">
                            3
                        </span>
                    </button>

                    <div className="flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container-high text-primary font-label-sm border border-outline-variant/50 shadow-sm">
                        <Icon
                            name="bolt"
                            className="text-[16px] text-primary"
                            filled
                        />

                        <span className="font-bold whitespace-nowrap">
                            Miễn phí • Ko cần đăng ký
                        </span>
                    </div>
                </div>
            </div>
        </header>
    );
}
