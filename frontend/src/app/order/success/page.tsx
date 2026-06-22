"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Suspense, useEffect, useState } from "react";
import { apiGetOrder, type Order } from "@/lib/api";
import Breadcrumbs from "@/components/Breadcrumbs";

function OrderSuccessContent() {
  const searchParams = useSearchParams();
  const orderId = searchParams.get("id");
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!orderId) {
      setLoading(false);
      return;
    }

    apiGetOrder(Number(orderId))
      .then((res) => setOrder(res.data))
      .catch(() => setOrder(null))
      .finally(() => setLoading(false));
  }, [orderId]);

  return (
    <div className="mx-auto max-w-2xl rounded-xl border border-[var(--border)] bg-white p-8 text-center">
      <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          className="h-8 w-8"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2}
        >
          <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 className="mb-2 text-2xl font-semibold">Заявка принята</h1>
      <p className="mb-6 text-[var(--text-muted)]">
        Спасибо! Менеджер свяжется с вами в ближайшее время для уточнения цены и наличия.
      </p>

      {order && (
        <div className="mb-6 rounded-lg bg-[var(--muted)] p-4 text-left text-sm">
          <p>
            <span className="text-[var(--text-muted)]">Номер заявки:</span>{" "}
            <span className="font-medium">#{order.id}</span>
          </p>
          <p>
            <span className="text-[var(--text-muted)]">Статус:</span>{" "}
            <span className="font-medium">{order.status_label}</span>
          </p>
          <p>
            <span className="text-[var(--text-muted)]">Количество товаров:</span>{" "}
            <span className="font-medium">{order.items_count}</span>
          </p>
          {order.total !== null && (
            <p>
              <span className="text-[var(--text-muted)]">Предварительная сумма:</span>{" "}
              <span className="font-medium">{order.total.toLocaleString("ru-RU")} ₽</span>
            </p>
          )}
        </div>
      )}

      {!loading && !order && orderId && (
        <p className="mb-6 text-sm text-[var(--text-muted)]">
          Подробности заявки доступны в личном кабинете после авторизации.
        </p>
      )}

      <div className="flex flex-col justify-center gap-3 sm:flex-row">
        <Link
          href="/"
          className="inline-flex rounded-lg bg-[#1a1a1a] px-6 py-2.5 font-medium text-white transition hover:bg-black"
        >
          На главную
        </Link>
        <Link
          href="/catalog/gadzhety"
          className="inline-flex rounded-lg border border-[var(--border)] px-6 py-2.5 font-medium transition hover:bg-[var(--muted)]"
        >
          В каталог
        </Link>
      </div>
    </div>
  );
}

export default function OrderSuccessPage() {
  const breadcrumbs = [
    { name: "Главная", url: "/" },
    { name: "Корзина", url: "/cart" },
    { name: "Заказ принят", url: "/order/success" },
  ];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />
      <Suspense
        fallback={
          <div className="mx-auto max-w-2xl rounded-xl border border-[var(--border)] bg-white p-8 text-center">
            Загрузка...
          </div>
        }
      >
        <OrderSuccessContent />
      </Suspense>
    </div>
  );
}
