import useTheme from "@/Hooks/useTheme";

export default function AppearanceSection() {

    const { theme, setTheme } = useTheme();

    return (
        <>
            <button onClick={() => setTheme("light")}>
                Light
            </button>

            <button onClick={() => setTheme("dark")}>
                Dark
            </button>
        </>
    );
}