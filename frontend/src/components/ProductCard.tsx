"use client";

import Link from "next/link";
import type { Product } from "@/lib/api";
import AddToCartButton from "./AddToCartButton";
import WishlistButton from "./WishlistButton";

function CameraIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
      <circle cx="12" cy="13" r="3" />
    </svg>
  );
}

function CheckIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="12"
      height="12"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="3"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}

export default function ProductCard({ product }: { product: Product }) {
  const image = product.images?.[0] || product.category?.image || "/images/placeholder-product.svg";
  const brand = product.brand;

  return (
    <div className="group relative flex flex-col rounded-xl border border-[var(--border)] bg-white p-3 transition hover:shadow-md">
      <Link href={product.url} className="block flex-1">
        <div className="relative mb-2 aspect-square overflow-hidden rounded-lg bg-[var(--muted)]">
          {image ? (
            <img
              src={image}
              alt={product.name}
              className="h-full w-full object-contain p-2 transition duration-300 group-hover:scale-105"
              loading="lazy"
            />
          ) : (
            <div className="flex h-full w-full flex-col items-center justify-center text-[var(--text-muted)]">
              <CameraIcon className="mb-1 opacity-30" />
              <span className="text-[10px]">Нет фото</span>
            </div>
          )}
          <span className="absolute left-2 top-2 rounded bg-[#ff4d4d] px-1.5 py-0.5 text-[10px] font-bold text-white">
            ХИТ
          </span>
        </div>

        {brand && (
          <div className="mb-1 text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">
            {brand}
          </div>
        )}

        <div className="mb-2 text-sm font-bold leading-tight text-[#1a1a1a]">
          Цена по запросу
        </div>
        <div className="mb-2 text-xs text-[var(--text-muted)]">
          <span className="font-semibold text-[#1a1a1a]">GB</span> + 0 бонусов
        </div>

        <div className="mb-2 line-clamp-2 text-xs font-medium leading-snug text-[#1a1a1a]">
          {product.name}
        </div>

        <div className="mb-3 flex items-center gap-1 text-xs font-medium text-green-600">
          <CheckIcon />
          В наличии
        </div>
      </Link>

      <div className="mt-auto flex items-center gap-2">
        <AddToCartButton productId={product.id} fullWidth className="py-2 text-sm" />
        <WishlistButton productId={product.id} className="h-9 w-9 shrink-0" />
      </div>
    </div>
  );
}
