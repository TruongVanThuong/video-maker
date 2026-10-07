import type { ModalDeal } from "../../../types";
import { Icon } from "./Icon";

interface Props {
    deal: ModalDeal | null;
    onClose: () => void;
    onProceed: () => void;
}

export function VoucherModal({ deal, onClose, onProceed }: Props) {
    if (!deal) return null;

    return (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div className="w-full max-w-sm bg-surface-container-lowest rounded-2xl p-5 shadow-2xl space-y-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <span className="w-3 h-3 rounded-full bg-secondary-container animate-pulse" />

                        <span className="font-headline-sm">
                            Voucher Sẵn Sàng
                        </span>
                    </div>

                    <button
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center"
                    >
                        <Icon name="close" className="text-[18px]" />
                    </button>
                </div>

                <div className="bg-surface-container p-3 rounded-xl space-y-2">
                    <p className="text-sm font-bold">{deal.title}</p>

                    <div className="flex items-center justify-between">
                        <span className="text-xs text-outline">
                            Giá sau áp mã:
                        </span>

                        <span className="text-xl text-error font-extrabold">
                            {deal.price}
                        </span>
                    </div>

                    <div className="flex items-center justify-between bg-surface-container-lowest px-3 py-2 rounded-lg">
                        <span className="text-[13px] tracking-wider text-primary font-bold">
                            {deal.code}
                        </span>

                        <span className="text-[11px] text-tertiary font-bold">
                            Mã đã lưu
                        </span>
                    </div>
                </div>

                <button
                    onClick={onProceed}
                    className="w-full h-12 rounded-xl bg-gradient-to-r from-secondary-container to-secondary-fixed-dim text-on-secondary-fixed font-headline-sm font-bold flex items-center justify-center gap-2 shadow-md"
                >
                    <Icon name="open_in_new" className="text-[20px]" />
                    Mở App Để Mua Ngay
                </button>

                <p className="text-[11px] text-center text-outline">
                    ⚡ Không cần đăng ký tài khoản • Mã tự sao chép & tự động áp
                    dụng khi mở App sàn
                </p>
            </div>
        </div>
    );
}
