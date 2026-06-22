"use client";

import Link from "next/link";
import { useEffect } from "react";
import { useAuthStore } from "@/stores/authStore";
import { useWishlistStore } from "@/stores/wishlistStore";
import Breadcrumbs from "@/components/Breadcrumbs";

export default function WishlistPage() {
  const user = useAuthStore((s) => s.user);
  const items = useWishlistStore((s) => s.items);
  const isLoading = useWishlistStore((s) => s.isLoading);
  const load = useWishlistStore((s) => s.load);
  const remove = useWishlistStore((s) => s.remove);

  useEffect(() => {
    if (user) load();
  }, [user, load]);

  const breadcrumbs = [
    { name: "Главная", url: "/" },
    { name: "Избранное", url: "/wishlist" },
  ];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />
      <h1 className="mb-6 text-3xl font-semibold">Избранное</h1>

      {!user ? (
        <div className="rounded-xl border border-[var(--border)] bg-white p-8 text-center">
          <p className="mb-4 text-[var(--text-muted)]">
            Войдите, чтобы увидеть избранные товары.
          </p>
          <Link
            href="/login"
            className="inline-flex rounded-lg bg-[#1a1a1a] px-6 py-2.5 font-medium text-white transition hover:bg-black"
          >
            Войти
          </Link>
        </div>
      ) : isLoading ? (
        <p className="text-[var(--text-muted)]">Загрузка...</p>
      ) : items.length === 0 ? (
        <div className="rounded-xl border border-[var(--border)] bg-white p-8 text-center">
          <p className="text-[var(--text-muted)]">Список избранного пуст.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {items.map((item) => (
            <div
              key={item.id}
              className="rounded-xl border border-[var(--border)] bg-white p-4"
            >
              <Link
                href={item.product.url || `/product/${item.product.slug}`}
                className="mb-3 block"
              >
                <div className="mb-3 flex h-40 items-center justify-center overflow-hidden rounded-lg bg-[var(--muted)]">
                  {item.product.image ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={item.product.image}
                      alt={item.product.name}
                      className="h-full w-full object-contain p-3"
                    />
                  ) : (
                    <span className="text-xs text-[var(--text-muted)]">Нет фото</span>
                  )}
                </div>
                <div className="font-medium leading-snug hover:text-[var(--accent)]">
                  {item.product.name}
                </div>
              </Link>
              <button
                onClick={() => remove(item.id)}
                className="text-sm text-red-500 hover:underline"
              >
                Удалить
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
