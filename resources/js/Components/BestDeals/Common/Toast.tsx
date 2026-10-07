import { Icon } from "./Icon";

interface Props {
    message: string;
    visible: boolean;
}

export function Toast({ message, visible }: Props) {
    return (
        <div
            className={`
        fixed top-20 inset-x-4 z-50
        flex justify-center
        pointer-events-none
        transition-all duration-300
        ${visible ? "translate-y-0 opacity-100" : "-translate-y-16 opacity-0"}
      `}
        >
            <div className="px-4 py-2.5 rounded-full bg-inverse-surface text-inverse-on-surface shadow-xl flex items-center gap-2 text-xs">
                <Icon
                    name="check_circle"
                    className="text-[18px] text-tertiary-fixed"
                />

                {message}
            </div>
        </div>
    );
}
