export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};

export type Marketplace = "all" | "shopee" | "tiktok" | "lazada";

export interface MarketplaceItem {
    id: Marketplace;
    name: string;
    shortName: string;
    count: string;
    color: string;
    badgeColor: string;
}

export interface Voucher {
    id: string;
    marketplace: Marketplace;
    marketplaceName: string;
    title: string;
    description: string;
    remaining: number;
    badge: string;
    badgeClass: string;
    actionLabel: string;
    code: string;
    icon?: string;
}

export interface Deal {
    id: string;
    title: string;
    marketplace: Marketplace;
    marketplaceName: string;

    image: string;

    originalPrice: number;
    salePrice: number;

    discount: string;

    voucherCode: string;

    quota?: number;
    tag?: string;
    tagClass?: string;
}

export interface ModalDeal {
    title: string;
    price: string;
    platform: string;
    code: string;
}
