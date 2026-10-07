import { useCallback, useState } from "react";

export function useToast() {
    const [message, setMessage] = useState("");
    const [visible, setVisible] = useState(false);

    const showToast = useCallback((text: string) => {
        setMessage(text);
        setVisible(true);

        window.setTimeout(() => {
            setVisible(false);
        }, 2200);
    }, []);

    return {
        message,
        visible,
        showToast,
    };
}
