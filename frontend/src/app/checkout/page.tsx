"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { useAuthStore } from "@/stores/authStore";
import { useCartStore } from "@/stores/cartStore";
import { apiCreateOrder } from "@/lib/api";
import Breadcrumbs from "@/components/Breadcrumbs";

export default function CheckoutPage() {
  const router = useRouter();
  const items = useCartStore((s) => s.items);
  const count = useCartStore((s) => s.count);
  const loadCart = useCartStore((s) => s.load);
  const user = useAuthStore((s) => s.user);

  const [form, setForm] = useState({
    customer_name: "",
    customer_phone: "",
    customer_email: "",
    customer_city: "",
    customer_comment: "",
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [globalError, setGlobalError] = useState("");

  useEffect(() => {
    loadCart();
  }, [loadCart]);

  useEffect(() => {
    if (user) {
      setForm((prev) => ({
        ...prev,
        customer_name: user.name || prev.customer_name,
        customer_phone: user.phone || prev.customer_phone,
        customer_email: user.email || prev.customer_email,
      }));
    }
  }, [user]);

  const breadcrumbs = [
    { name: "Главная", url: "/" },
    { name: "Корзина", url: "/cart" },
    { name: "Оформление заказа", url: "/checkout" },
  ];

  if (count === 0 && items.length === 0) {
    return (
      <div className="container-theme pb-12">
        <Breadcrumbs items={breadcrumbs} />
        <h1 className="mb-6 text-3xl font-semibold">Оформление заказа</h1>
        <div className="rounded-xl border border-[var(--border)] bg-white p-8 text-center">
          <p className="mb-4 text-[var(--text-muted)]">Ваша корзина пуста.</p>
          <Link
            href="/catalog/gadzhety"
            className="inline-flex rounded-lg bg-[#1a1a1a] px-6 py-2.5 font-medium text-white transition hover:bg-black"
          >
            Перейти в каталог
          </Link>
        </div>
      </div>
    );
  }

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    setErrors((prev) => ({ ...prev, [name]: "" }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setGlobalError("");
    setErrors({});

    try {
      const res = await apiCreateOrder({
        customer_name: form.customer_name,
        customer_phone: form.customer_phone,
        customer_email: form.customer_email,
        customer_city: form.customer_city,
        customer_comment: form.customer_comment,
      });
      await loadCart();
      router.push(`/order/success?id=${res.data.id}`);
    } catch (err) {
      const message = err instanceof Error ? err.message : "Ошибка оформления заказа";
      if (message.toLowerCase().includes("корзина")) {
        setGlobalError("Корзина пуста. Добавьте товары и попробуйте снова.");
      } else {
        setGlobalError(message);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />
      <h1 className="mb-6 text-3xl font-semibold">Оформление заказа</h1>

      <div className="grid gap-8 lg:grid-cols-3">
        <form onSubmit={handleSubmit} className="space-y-4 lg:col-span-2">
          {globalError && (
            <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
              {globalError}
            </div>
          )}

          <div className="rounded-xl border border-[var(--border)] bg-white p-6">
            <h2 className="mb-4 text-lg font-semibold">Контактные данные</h2>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="mb-1 block text-sm text-[var(--text-muted)]">
                  Имя <span className="text-red-500">*</span>
                </label>
                <input
                  name="customer_name"
                  value={form.customer_name}
                  onChange={handleChange}
                  required
                  className="w-full rounded-lg border border-[var(--border)] px-4 py-2 outline-none focus:border-[var(--accent)]"
                />
                {errors.customer_name && (
                  <p className="mt-1 text-xs text-red-500">{errors.customer_name}</p>
                )}
              </div>

              <div>
                <label className="mb-1 block text-sm text-[var(--text-muted)]">
                  Телефон <span className="text-red-500">*</span>
                </label>
                <input
                  name="customer_phone"
                  type="tel"
                  value={form.customer_phone}
                  onChange={handleChange}
                  required
                  placeholder="+7 (___) ___-__-__"
                  className="w-full rounded-lg border border-[var(--border)] px-4 py-2 outline-none focus:border-[var(--accent)]"
                />
                {errors.customer_phone && (
                  <p className="mt-1 text-xs text-red-500">{errors.customer_phone}</p>
                )}
              </div>

              <div>
                <label className="mb-1 block text-sm text-[var(--text-muted)]">Email</label>
                <input
                  name="customer_email"
                  type="email"
                  value={form.customer_email}
                  onChange={handleChange}
                  className="w-full rounded-lg border border-[var(--border)] px-4 py-2 outline-none focus:border-[var(--accent)]"
                />
              </div>

              <div>
                <label className="mb-1 block text-sm text-[var(--text-muted)]">Город</label>
                <input
                  name="customer_city"
                  value={form.customer_city}
                  onChange={handleChange}
                  className="w-full rounded-lg border border-[var(--border)] px-4 py-2 outline-none focus:border-[var(--accent)]"
                />
              </div>
            </div>

            <div className="mt-4">
              <label className="mb-1 block text-sm text-[var(--text-muted)]">Комментарий</label>
              <textarea
                name="customer_comment"
                value={form.customer_comment}
                onChange={handleChange}
                rows={4}
                className="w-full rounded-lg border border-[var(--border)] px-4 py-2 outline-none focus:border-[var(--accent)]"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full rounded-lg bg-[#1a1a1a] py-3 font-medium text-white transition hover:bg-black disabled:opacity-60 sm:w-auto sm:px-12"
          >
            {isSubmitting ? "Отправка..." : "Отправить заявку"}
          </button>
        </form>

        <div className="h-fit rounded-xl border border-[var(--border)] bg-white p-6">
          <h2 className="mb-4 text-lg font-semibold">Ваш заказ</h2>
          <ul className="mb-4 space-y-3">
            {items.map((item) => (
              <li key={item.id} className="flex justify-between text-sm">
                <span className="text-[var(--text-muted)]">
                  {item.product.name}
                  {item.offer && (
                    <span className="block text-xs">{item.offer.name}</span>
                  )}
                  <span className="text-xs">× {item.quantity}</span>
                </span>
              </li>
            ))}
          </ul>
          <p className="text-sm text-[var(--text-muted)]">
            Цены уточняются менеджером после получения заявки.
          </p>
        </div>
      </div>
    </div>
  );
}
