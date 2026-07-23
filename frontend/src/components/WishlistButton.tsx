"use client";

import { useEffect } from "react";
import { useAuthStore } from "@/stores/authStore";
import { useWishlistStore } from "@/stores/wishlistStore";
import { toast } from "sonner";

export default function WishlistButton({
  productId,
  className = "",
}: {
  productId: number;
  className?: string;
}) {
  const user = useAuthStore((s) => s.user);
  const items = useWishlistStore((s) => s.items);
  const toggle = useWishlistStore((s) => s.toggle);
  const load = useWishlistStore((s) => s.load);
  const isFavorite = items.some((i) => i.product.id === productId);

  useEffect(() => {
    if (user) load();
  }, [user, load]);

  const handleClick = async () => {
    if (!user) {
      toast.info("Войдите, чтобы добавить в избранное");
      return;
    }
    try {
      await toggle(productId);
      toast.success(isFavorite ? "Удалено из избранного" : "Добавлено в избранное");
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Ошибка");
    }
  };

  return (
    <button
      onClick={handleClick}
      className={`flex h-10 w-10 items-center justify-center rounded-lg border border-[var(--border)] transition hover:border-[var(--accent)] ${
        isFavorite ? "text-red-500" : "text-[var(--text-muted)]"
      } ${className}`}
      title={isFavorite ? "В избранном" : "Добавить в избранное"}
    >
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill={isFavorite ? "currentColor" : "none"}
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
      </svg>
    </button>
  );
}
