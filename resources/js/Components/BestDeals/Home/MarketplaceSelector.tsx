import { marketplaces } from "../../../Datas/BestDeals/marketplaces";
import type { Marketplace } from "../../../types";
import { Icon } from "../Common/Icon";

interface Props {
  value: Marketplace;
  onChange: (value: Marketplace) => void;
}

export function MarketplaceSelector({
  value,
  onChange,
}: Props) {
  return (
    <section className="flex flex-col space-y-2">
      <div className="flex items-center justify-between px-margin-mobile">
        <span className="font-headline-sm text-on-surface">
          Sàn Thương Mại
        </span>

        <span className="text-xs text-primary font-bold">
          14.850 mã hoạt động
        </span>
      </div>

      <div className="flex items-center gap-2 px-margin-mobile overflow-x-auto no-scrollbar py-1">
        {marketplaces.map((marketplace) => {
          const active = value === marketplace.id;

          return (
            <button
              key={marketplace.id}
              onClick={() => onChange(marketplace.id)}
              className={`
                shrink-0 px-3.5 py-2 rounded-full
                flex items-center gap-2
                text-xs font-semibold
                transition-colors
                ${
                  active
                    ? "bg-primary-container text-white"
                    : "bg-surface-container-lowest text-on-surface"
                }
              `}
            >
              {marketplace.id === "all" ? (
                <Icon name="apps" className="text-[18px]" />
              ) : (
                <span
                  className={`w-5 h-5 rounded-full ${marketplace.color} flex items-center justify-center text-white text-[10px] font-black`}
                >
                  {marketplace.shortName}
                </span>
              )}

              <span>{marketplace.name}</span>

              <span
                className={
                  active
                    ? "px-1.5 py-0.5 rounded-full bg-white/20"
                    : `px-1.5 py-0.5 rounded-full ${marketplace.badgeColor}`
                }
              >
                {marketplace.count}
              </span>
            </button>
          );
        })}
      </div>
    </section>
  );
}
