import { vouchers } from "../../../Datas/BestDeals/vouchers";
import { useCountdown } from "../../../Hooks/BestDeals/useCountdown";
import type { Voucher } from "../../../types";
import { Icon } from "../Common/Icon";
import { VoucherCard } from "./VoucherCard";

interface Props {
    onCopy: (voucher: Voucher) => void;
}

export function FlashVoucherSection({ onCopy }: Props) {
    const countdown = useCountdown(2 * 3600 + 45 * 60 + 18);

    return (
        <section className="mx-margin-mobile flex flex-col space-y-2.5">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <span className="flex items-center justify-center w-6 h-6 rounded-md bg-error text-white">
                        <Icon
                            name="local_fire_department"
                            className="text-[18px]"
                        />
                    </span>

                    <h2 className="font-headline-sm">Săn Mã Giờ Vàng</h2>
                </div>

                <div className="flex items-center gap-1 px-2.5 py-1 rounded-full bg-error-container text-on-error-container text-xs font-bold">
                    <Icon name="timer" className="text-[14px]" />

                    {countdown.formatted}
                </div>
            </div>

            <div className="flex gap-space-sm overflow-x-auto no-scrollbar py-1">
                {vouchers.map((voucher) => (
                    <VoucherCard
                        key={voucher.id}
                        voucher={voucher}
                        onCopy={onCopy}
                    />
                ))}
            </div>
        </section>
    );
}
