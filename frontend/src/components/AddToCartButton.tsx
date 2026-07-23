"use client";

import { useState } from "react";
import { useCartStore } from "@/stores/cartStore";
import { toast } from "sonner";

export default function AddToCartButton({
  productId,
  offerId,
  fullWidth = false,
  className = "",
}: {
  productId: number;
  offerId?: number;
  fullWidth?: boolean;
  className?: string;
}) {
  const add = useCartStore((s) => s.add);
  const [loading, setLoading] = useState(false);

  const handleClick = async () => {
    setLoading(true);
    try {
      await add({ product_id: productId, offer_id: offerId, quantity: 1 });
      toast.success("Товар добавлен в корзину");
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Не удалось добавить");
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleClick}
      disabled={loading}
      className={`rounded-lg bg-[#1a1a1a] font-medium text-white transition hover:bg-black disabled:opacity-60 ${
        fullWidth ? "w-full" : ""
      } ${className}`}
    >
      {loading ? "Добавляем..." : "В корзину"}
    </button>
  );
}
