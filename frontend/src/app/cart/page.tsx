"use client";

import Link from "next/link";
import { useEffect } from "react";
import { useCartStore } from "@/stores/cartStore";
import Breadcrumbs from "@/components/Breadcrumbs";

export default function CartPage() {
  const items = useCartStore((s) => s.items);
  const count = useCartStore((s) => s.count);
  const isLoading = useCartStore((s) => s.isLoading);
  const load = useCartStore((s) => s.load);
  const updateQty = useCartStore((s) => s.updateQty);
  const remove = useCartStore((s) => s.remove);
  const clear = useCartStore((s) => s.clear);

  useEffect(() => {
    load();
  }, [load]);

  const breadcrumbs = [
    { name: "Главная", url: "/" },
    { name: "Корзина", url: "/cart" },
  ];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />
      <h1 className="mb-6 text-3xl font-semibold">
        Корзина
        <span className="ml-3 text-base font-normal text-[var(--text-muted)]">
          {count} товаров
        </span>
      </h1>

      {isLoading ? (
        <p className="text-[var(--text-muted)]">Загрузка...</p>
      ) : items.length === 0 ? (
        <div className="rounded-xl border border-[var(--border)] bg-white p-8 text-center">
          <p className="mb-4 text-[var(--text-muted)]">Ваша корзина пуста.</p>
          <Link
            href="/catalog/gadzhety"
            className="inline-flex rounded-lg bg-[#1a1a1a] px-6 py-2.5 font-medium text-white transition hover:bg-black"
          >
            Перейти в каталог
          </Link>
        </div>
      ) : (
        <div className="grid gap-8 lg:grid-cols-3">
          <div className="space-y-4 lg:col-span-2">
            {items.map((item) => (
              <div
                key={item.id}
                className="flex gap-4 rounded-xl border border-[var(--border)] bg-white p-4"
              >
                <div className="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-[var(--border)] bg-[var(--muted)]">
                  {item.product.image ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={item.product.image}
                      alt={item.product.name}
                      className="h-full w-full object-contain p-2"
                    />
                  ) : (
                    <span className="text-xs text-[var(--text-muted)]">Нет фото</span>
                  )}
                </div>
                <div className="flex flex-1 flex-col justify-between">
                  <Link
                    href={item.product.url || `/product/${item.product.slug}`}
                    className="font-medium hover:text-[var(--accent)]"
                  >
                    {item.product.name}
                  </Link>
                  {item.offer && (
                    <p className="text-sm text-[var(--text-muted)]">
                      {item.offer.name}
                    </p>
                  )}
                  <div className="flex items-center gap-3">
                    <div className="flex items-center rounded-lg border border-[var(--border)]">
                      <button
                        onClick={() => updateQty(item.id, Math.max(1, item.quantity - 1))}
                        className="px-3 py-1 hover:bg-[var(--muted)]"
                      >
                        −
                      </button>
                      <span className="min-w-[2rem] text-center">{item.quantity}</span>
                      <button
                        onClick={() => updateQty(item.id, item.quantity + 1)}
                        className="px-3 py-1 hover:bg-[var(--muted)]"
                      >
                        +
                      </button>
                    </div>
                    <button
                      onClick={() => remove(item.id)}
                      className="text-sm text-red-500 hover:underline"
                    >
                      Удалить
                    </button>
                  </div>
                </div>
              </div>
            ))}
            <button
              onClick={() => clear()}
              className="text-sm text-[var(--text-muted)] hover:text-red-500"
            >
              Очистить корзину
            </button>
          </div>

          <div className="rounded-xl border border-[var(--border)] bg-white p-6">
            <h2 className="mb-4 text-lg font-semibold">Итого</h2>
            <p className="mb-6 text-[var(--text-muted)]">
              Цены уточняйте у менеджера при оформлении заказа.
            </p>
            <Link
              href="/checkout"
              className="block w-full rounded-lg bg-[#1a1a1a] py-3 text-center font-medium text-white transition hover:bg-black"
            >
              Оформить заказ
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
