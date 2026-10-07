import { Icon } from "../Common/Icon";

const items = [
    {
        label: "Trang Chủ",
        icon: "explore",
    },
    {
        label: "Săn Mã",
        icon: "confirmation_number",
    },
    {
        label: "Giá Hot",
        icon: "local_fire_department",
    },
    {
        label: "Đã Lưu",
        icon: "bookmark_heart",
    },
    {
        label: "Quản Trị",
        icon: "admin_panel_settings",
    },
];

export function BottomNavigation() {
    return (
        <nav className="fixed bottom-0 w-full z-50 pb-safe bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_-2px_12px_rgba(0,0,0,0.05)]">
            <div className="flex justify-around items-center h-16 px-space-xs">
                {items.map((item, index) => (
                    <button
                        key={item.label}
                        className={`
              relative flex flex-col items-center
              justify-center min-w-[56px] min-h-[44px]
              transition-all
              ${
                  index === 0
                      ? "text-primary-container font-bold"
                      : "text-on-surface-variant"
              }
            `}
                    >
                        <Icon name={item.icon} className="text-[24px]" />

                        <span className="text-[11px] mt-0.5">{item.label}</span>
                    </button>
                ))}
            </div>
        </nav>
    );
}
