import { deals } from "../../../Datas/BestDeals/deals";
import type { Deal, Marketplace } from "../../../types";
import { DealCard } from "./DealCard";

interface Props {
    marketplace: Marketplace;
    onSelect: (deal: Deal) => void;
}

export function DealFeed({ marketplace, onSelect }: Props) {
    const filteredDeals =
        marketplace === "all"
            ? deals
            : deals.filter((deal) => deal.marketplace === marketplace);

    return (
        <section className="mx-margin-mobile">
            <div className="flex items-center justify-between mb-2.5">
                <h3 className="font-headline-sm">Top Deal Gom Mã Hôm Nay</h3>

                <span className="text-[11px] text-outline">
                    Cập nhật 1 phút trước
                </span>
            </div>

            <div className="grid grid-cols-2 gap-space-sm">
                {filteredDeals.map((deal) => (
                    <DealCard key={deal.id} deal={deal} onSelect={onSelect} />
                ))}
            </div>
        </section>
    );
}
