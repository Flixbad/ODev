"use client";

import {
  motion,
  useMotionValue,
  useSpring,
  useReducedMotion,
} from "framer-motion";
import {
  type MouseEvent,
  type ReactNode,
  type RefObject,
  useRef,
} from "react";
import { cn } from "@/lib/cn";

type MagneticButtonProps = {
  children: ReactNode;
  href?: string;
  onClick?: () => void;
  className?: string;
  variant?: "primary" | "ghost" | "ink";
  type?: "button" | "submit";
};

export function MagneticButton({
  children,
  href,
  onClick,
  className,
  variant = "primary",
  type = "button",
}: MagneticButtonProps) {
  const ref = useRef<HTMLAnchorElement | HTMLButtonElement>(null);
  const reduce = useReducedMotion();
  const x = useMotionValue(0);
  const y = useMotionValue(0);
  const springX = useSpring(x, { stiffness: 220, damping: 18 });
  const springY = useSpring(y, { stiffness: 220, damping: 18 });

  const onMove = (e: MouseEvent) => {
    if (reduce || !ref.current) return;
    const rect = ref.current.getBoundingClientRect();
    const dx = e.clientX - (rect.left + rect.width / 2);
    const dy = e.clientY - (rect.top + rect.height / 2);
    x.set(dx * 0.28);
    y.set(dy * 0.28);
  };

  const onLeave = () => {
    x.set(0);
    y.set(0);
  };

  const styles = {
    primary:
      "bg-signal text-white hover:bg-signal-hot shadow-[0_12px_40px_rgba(230,51,18,0.28)]",
    ghost:
      "bg-transparent text-ink border border-[var(--line-strong)] hover:border-ink hover:bg-ink hover:text-paper",
    ink: "bg-ink text-paper hover:bg-ink-soft",
  }[variant];

  const shared = cn(
    "relative inline-flex items-center justify-center gap-2 px-7 py-3.5 text-sm font-semibold tracking-wide transition-colors duration-300",
    styles,
    className,
  );

  if (href) {
    return (
      <motion.a
        ref={ref as RefObject<HTMLAnchorElement>}
        href={href}
        style={{ x: springX, y: springY }}
        onMouseMove={onMove}
        onMouseLeave={onLeave}
        className={shared}
      >
        {children}
      </motion.a>
    );
  }

  return (
    <motion.button
      ref={ref as RefObject<HTMLButtonElement>}
      type={type}
      onClick={onClick}
      style={{ x: springX, y: springY }}
      onMouseMove={onMove}
      onMouseLeave={onLeave}
      className={shared}
    >
      {children}
    </motion.button>
  );
}
