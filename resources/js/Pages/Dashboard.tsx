import { useState } from "react";

// import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

import { Header } from "@/Components/BestDeals/Layout/Header";
import { BottomNavigation } from "@/Components/BestDeals/Layout/BottomNavigation";
import { FloatingSearchBar } from "@/Components/BestDeals/Layout/FloatingSearchBar";

import { LiveAlert } from "@/Components/BestDeals/Home/LiveAlert";
import { DealHunter } from "@/Components/BestDeals/Home/DealHunter";
import { TrendingKeywords } from "@/Components/BestDeals/Home/TrendingKeywords";
import { MarketplaceSelector } from "@/Components/BestDeals/Home/MarketplaceSelector";
import { FlashVoucherSection } from "@/Components/BestDeals/Home/FlashVoucherSection";
import { DealFilters } from "@/Components/BestDeals/Home/DealFilters";
import { DealFeed } from "@/Components/BestDeals/Home/DealFeed";

import { Toast } from "@/Components/BestDeals/Common/Toast";
import { VoucherModal } from "@/Components/BestDeals/Common/VoucherModal";

import { useClipboard } from "@/Hooks/BestDeals/useClipboard";
import { useToast } from "@/Hooks/BestDeals/useToast";

import type {
    Deal,
    Marketplace,
    ModalDeal,
    Voucher,
} from "@/types";

export default function Dashboard() {
    const [marketplace, setMarketplace] =
        useState<Marketplace>("all");

    const [filter, setFilter] =
        useState("discount");

    const [modalDeal, setModalDeal] =
        useState<ModalDeal | null>(null);

    const {
        message,
        visible,
        showToast,
    } = useToast();

    const { copy } = useClipboard();

    const handleCopyVoucher = async (
        voucher: Voucher
    ) => {
        await copy(voucher.code);

        showToast(
            `Đã sao chép mã: ${voucher.code}`
        );
    };

    const handleDealSelect = async (deal: Deal) => {
        await copy(deal.voucherCode);

        setModalDeal({
            title: deal.title,
            price: `${new Intl.NumberFormat("vi-VN").format(
                deal.salePrice
            )}đ`,
            platform: deal.marketplaceName,
            code: deal.voucherCode,
        });
    };

    const handleHunt = async (value: string) => {
        if (!value.trim()) {
            showToast(
                "Vui lòng nhập hoặc dán link sản phẩm trước!"
            );

            return;
        }

        showToast(
            "Đang quét 12 voucher tương thích cho link..."
        );

        await new Promise((resolve) =>
            setTimeout(resolve, 700)
        );

        await copy("AUTO_HUNT_BEST");

        setModalDeal({
            title:
                value.length > 25
                    ? `${value.substring(0, 25)}...`
                    : value,
            price: "Tiết kiệm tối đa 35%",
            platform: "Shopee/TikTok",
            code: "AUTO_HUNT_BEST",
        });
    };

    const handleTrending = (keyword: string) => {
        showToast(`Đã chọn: ${keyword}`);
    };

    const handleProceed = () => {
        setModalDeal(null);

        showToast(
            "Đang chuyển hướng sang ứng dụng đối tác..."
        );
    };

    return (
        <div>
            <Head title="Dashboard" />

            <div className="min-h-screen bg-surface text-on-surface">
                <Header />

                <main className="w-full pt-16 pb-32 min-h-screen">
                    <div className="flex flex-col w-full pb-6 space-y-4">
                        <LiveAlert />

                        <DealHunter
                            onHunt={handleHunt}
                        />

                        <TrendingKeywords
                            onSelect={handleTrending}
                        />

                        <MarketplaceSelector
                            value={marketplace}
                            onChange={setMarketplace}
                        />

                        <FlashVoucherSection
                            onCopy={handleCopyVoucher}
                        />

                        <DealFilters
                            active={filter}
                            onChange={setFilter}
                        />

                        <DealFeed
                            marketplace={marketplace}
                            onSelect={handleDealSelect}
                        />
                    </div>
                </main>

                <Toast
                    message={message}
                    visible={visible}
                />

                <VoucherModal
                    deal={modalDeal}
                    onClose={() => setModalDeal(null)}
                    onProceed={handleProceed}
                />

                <FloatingSearchBar
                    onClick={() => {
                        window.scrollTo({
                            top: 0,
                            behavior: "smooth",
                        });
                    }}
                />

                <BottomNavigation />
            </div>
        </div>
    );
}
