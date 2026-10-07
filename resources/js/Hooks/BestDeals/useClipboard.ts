import { useCallback } from "react";

export function useClipboard() {
    const copy = useCallback(async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch {
            return false;
        }
    }, []);

    const paste = useCallback(async () => {
        try {
            return await navigator.clipboard.readText();
        } catch {
            return "";
        }
    }, []);

    return {
        copy,
        paste,
    };
}
