"use client";

import { toast } from "sonner";
import { type ButtonHTMLAttributes, type ReactNode } from "react";

type ComingSoonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  children: ReactNode;
  label?: string;
};

export default function ComingSoon({
  children,
  label = "Функция скоро появится",
  ...props
}: ComingSoonProps) {
  return (
    <button
      type="button"
      onClick={() => toast.info(label)}
      {...props}
    >
      {children}
    </button>
  );
}
