import { useEffect, useState } from "react";

export function useCountdown(initialSeconds: number) {
    const [seconds, setSeconds] = useState(initialSeconds);

    useEffect(() => {
        const timer = window.setInterval(() => {
            setSeconds((current) => Math.max(0, current - 1));
        }, 1000);

        return () => window.clearInterval(timer);
    }, []);

    const hours = Math.floor(seconds / 3600);

    const minutes = Math.floor((seconds % 3600) / 60);

    const secs = seconds % 60;

    return {
        hours: String(hours).padStart(2, "0"),
        minutes: String(minutes).padStart(2, "0"),
        seconds: String(secs).padStart(2, "0"),
        formatted: `${String(hours).padStart(2, "0")}:${String(
            minutes,
        ).padStart(2, "0")}:${String(secs).padStart(2, "0")}`,
    };
}
