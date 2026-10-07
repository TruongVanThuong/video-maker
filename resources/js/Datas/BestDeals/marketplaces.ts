import type { MarketplaceItem } from "../../types";

export const marketplaces: MarketplaceItem[] = [
    {
        id: "all",
        name: "Tất Cả",
        shortName: "ALL",
        count: "14.850",
        color: "bg-primary-container",
        badgeColor: "bg-white/20",
    },
    {
        id: "shopee",
        name: "Shopee",
        shortName: "S",
        count: "5.2k",
        color: "bg-orange-600",
        badgeColor: "bg-orange-100 text-orange-700",
    },
    {
        id: "tiktok",
        name: "TikTok Shop",
        shortName: "TT",
        count: "3.8k",
        color: "bg-neutral-900",
        badgeColor: "bg-neutral-200 text-neutral-800",
    },
    {
        id: "lazada",
        name: "Lazada",
        shortName: "L",
        count: "2.9k",
        color: "bg-blue-900",
        badgeColor: "bg-blue-100 text-blue-800",
    },
];
