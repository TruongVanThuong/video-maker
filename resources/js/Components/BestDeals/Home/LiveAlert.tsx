export function LiveAlert() {
    return (
        <div className="mx-margin-mobile px-space-md py-space-xs rounded-full bg-surface-container-high flex items-center justify-between shadow-sm">
            <div className="flex items-center gap-space-xs min-w-0">
                <span className="relative flex h-2.5 w-2.5 shrink-0">
                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-container opacity-75" />

                    <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary" />
                </span>

                <p className="font-body-sm text-body-sm text-on-surface truncate">
                    <span className="font-bold text-primary">
                        Shopee vừa nhả mã 50%
                    </span>{" "}
                    • 142 người đang săn
                </p>
            </div>

            <span className="font-label-sm text-primary-container shrink-0 pl-space-xs font-bold">
                Xem ngay →
            </span>
        </div>
    );
}
