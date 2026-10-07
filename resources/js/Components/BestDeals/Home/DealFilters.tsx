import { Icon } from "../Common/Icon";

const filters = [
    {
        id: "discount",
        label: "Giảm sâu nhất (%)",
        icon: "tune",
    },
    {
        id: "price",
        label: "Giá rẻ nhất",
    },
    {
        id: "hot",
        label: "Đang hot nhất",
        icon: "local_fire_department",
    },
    {
        id: "shipping",
        label: "Freeship 0đ",
    },
];

interface Props {
    active: string;
    onChange: (id: string) => void;
}

export function DealFilters({ active, onChange }: Props) {
    return (
        <div className="mx-margin-mobile pt-1 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            {filters.map((filter) => {
                const isActive = active === filter.id;

                return (
                    <button
                        key={filter.id}
                        onClick={() => onChange(filter.id)}
                        className={`
              shrink-0 px-3.5 py-1.5 rounded-full
              text-[11px] flex items-center gap-1 shadow-sm
              ${
                  isActive
                      ? "bg-primary-container text-white"
                      : "bg-surface-container-lowest text-on-surface-variant"
              }
            `}
                    >
                        {filter.icon && (
                            <Icon name={filter.icon} className="text-[14px]" />
                        )}

                        {filter.label}
                    </button>
                );
            })}
        </div>
    );
}
