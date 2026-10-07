import type { Deal } from "../../../types";
import { Icon } from "../Common/Icon";

interface Props {
    deal: Deal;
    onSelect: (deal: Deal) => void;
}

export function DealCard({ deal, onSelect }: Props) {
    const discountPercent = Math.round(
        ((deal.originalPrice - deal.salePrice) / deal.originalPrice) * 100,
    );

    const formatPrice = (price: number) =>
        new Intl.NumberFormat("vi-VN").format(price);

    return (
        <article className="rounded-xl bg-surface-container-lowest p-2.5 flex flex-col shadow-md relative overflow-hidden group">
            <div className="absolute top-2 left-2 z-10 px-1.5 py-0.5 rounded-md bg-orange-600/90 text-white text-[11px] font-bold">
                {deal.marketplaceName}
            </div>

            <div className="absolute top-2 right-2 z-10 px-1.5 py-0.5 rounded-md bg-secondary-container text-on-secondary-fixed text-[11px] font-extrabold">
                {deal.discount}
            </div>

            <div className="relative w-full aspect-square rounded-lg overflow-hidden bg-surface-container mb-2">
                <img
                    src={deal.image}
                    alt={deal.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
            </div>

            <div className="flex flex-col flex-1 justify-between space-y-1.5">
                <p className="text-sm text-on-surface font-bold line-clamp-2 leading-snug">
                    {deal.title}
                </p>

                <div className="flex flex-col">
                    <span className="text-xs text-outline line-through">
                        {formatPrice(deal.originalPrice)}đ
                    </span>

                    <div className="flex items-baseline gap-1">
                        <span className="text-xl text-error font-extrabold">
                            {formatPrice(deal.salePrice)}
                        </span>

                        <span className="text-xs text-error font-bold">đ</span>
                    </div>
                </div>

                {deal.quota !== undefined ? (
                    <div className="w-full space-y-1">
                        <div className="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div
                                className="bg-error h-full rounded-full"
                                style={{
                                    width: `${deal.quota}%`,
                                }}
                            />
                        </div>

                        <div className="text-[11px] text-outline flex justify-between">
                            <span>Đã dùng {deal.quota}%</span>

                            <span className="text-error font-bold">
                                {deal.quota >= 80 ? "Sắp hết" : ""}
                            </span>
                        </div>
                    </div>
                ) : deal.tag ? (
                    <span
                        className={`inline-flex w-fit px-1.5 py-0.5 rounded text-[11px] font-bold ${deal.tagClass}`}
                    >
                        {deal.tag}
                    </span>
                ) : (
                    <span className="text-xs text-transparent">-</span>
                )}

                <button
                    onClick={() => onSelect(deal)}
                    className="w-full h-9 mt-1 rounded-lg bg-primary-container text-white text-xs font-bold flex items-center justify-center gap-1 shadow-sm active:scale-95 transition-all"
                >
                    Lấy Mã Săn
                    <Icon name="arrow_forward" className="text-[16px]" />
                </button>
            </div>
        </article>
    );
}
