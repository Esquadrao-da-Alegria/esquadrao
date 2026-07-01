import { cn } from "@/lib/utils";
import type { FC, ReactNode } from "react";
import { useEffect, useLayoutEffect, useState } from "react";
import ReactDOM from "react-dom";

const ANIM_MS = 300;

interface Props {
    isOpen: boolean;
    onClose: () => void;
    children: ReactNode;
    /** Classes extras no painel branco (ex.: max-w-xl). */
    className?: string;
}

const Modal: FC<Props> = ({ isOpen, onClose, children, className }) => {
    const [present, setPresent] = useState(isOpen);

    useLayoutEffect(() => {
        if (isOpen) {
            setPresent(true);
        }
    }, [isOpen]);

    useEffect(() => {
        if (!isOpen && present) {
            const t = window.setTimeout(() => setPresent(false), ANIM_MS);
            return () => window.clearTimeout(t);
        }
    }, [isOpen, present]);

    if (!present) {
        return null;
    }

    const exiting = !isOpen && present;

    return ReactDOM.createPortal(
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                className={cn(
                    "absolute inset-0 bg-black/50 backdrop-blur-sm duration-300",
                    exiting
                        ? "animate-out fade-out-0"
                        : "animate-in fade-in",
                )}
                onClick={onClose}
            />

            <div
                className={cn(
                    "relative w-full max-w-4xl origin-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl duration-300 ease-out",
                    exiting
                        ? "animate-out fade-out-0 zoom-out-95"
                        : "animate-in fade-in zoom-in-95",
                    className,
                )}
            >
                {children}
            </div>
        </div>,
        document.body,
    );
};

export default Modal;
