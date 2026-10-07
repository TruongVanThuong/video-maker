import type { Voucher } from "../../../types";
import { Icon } from "../Common/Icon";

interface Props {
    voucher: Voucher;
    onCopy: (voucher: Voucher) => void;
}

export function VoucherCard({ voucher, onCopy }: Props) {
    return (
        <div className="w-64 shrink-0 rounded-xl bg-surface-container-lowest p-3 shadow-md flex flex-col justify-between relative overflow-hidden">
            <div className="absolute top-0 right-0 w-16 h-16 bg-orange-100/60 rounded-bl-full" />

            <div className="flex items-start justify-between">
                <div className="flex items-center gap-1.5">
                    <span className="w-4 h-4 rounded-full bg-orange-600 text-white font-black text-[9px] flex items-center justify-center">
                        {voucher.marketplace === "tiktok"
                            ? "TT"
                            : voucher.marketplace === "lazada"
                              ? "L"
                              : "S"}
                    </span>

                    <span className="text-[11px] uppercase font-bold">
                        {voucher.marketplaceName}
                    </span>
                </div>

                <span
                    className={`px-1.5 py-0.5 rounded text-[11px] font-bold ${voucher.badgeClass}`}
                >
                    {voucher.badge}
                </span>
            </div>

            <div className="my-2">
                <p className="text-lg font-extrabold leading-tight">
                    {voucher.title}
                </p>

                <p className="text-xs text-outline truncate">
                    {voucher.description}
                </p>
            </div>

            <div className="flex items-center justify-between pt-2">
                <span className="text-[11px] text-outline">
                    Còn {voucher.remaining}% lượt
                </span>

                <button
                    onClick={() => onCopy(voucher)}
                    className="h-8 px-3 rounded-full bg-primary-container text-white text-xs flex items-center gap-1 shadow-sm"
                >
                    <Icon
                        name={voucher.icon ?? "content_copy"}
                        className="text-[14px]"
                    />

                    {voucher.actionLabel}
                </button>
            </div>
        </div>
    );
}
