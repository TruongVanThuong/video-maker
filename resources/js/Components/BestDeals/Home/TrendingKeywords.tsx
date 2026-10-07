import { trendingKeywords } from "../../../Datas/BestDeals/trending";

interface Props {
    onSelect: (keyword: string) => void;
}

export function TrendingKeywords({ onSelect }: Props) {
    return (
        <div className="mx-margin-mobile flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <span className="text-xs text-outline shrink-0">Trending:</span>

            {trendingKeywords.map((keyword) => (
                <button
                    key={keyword}
                    onClick={() => onSelect(keyword)}
                    className="shrink-0 px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-xs hover:bg-primary-fixed hover:text-on-primary-fixed"
                >
                    {keyword}
                </button>
            ))}
        </div>
    );
}
