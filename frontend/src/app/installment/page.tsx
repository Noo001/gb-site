import Link from "next/link";

export const metadata = {
  title: "Рассрочка — GADGET·BAR",
  description: "Покупайте гаджеты в рассрочку 0% до 36 месяцев.",
};

export default function InstallmentPage() {
  return (
    <div className="container-theme pb-12">
      <div className="rounded-2xl bg-gradient-to-br from-[#e0f7fa] to-[#b2ebf2] p-8 md:p-12">
        <div className="mx-auto max-w-2xl text-center">
          <div className="mb-4 text-xs font-semibold uppercase tracking-wide text-[#00838f]">
            ХАЛВА | GADGET-BAR
          </div>
          <h1 className="mb-4 text-3xl font-bold md:text-5xl">
            Рассрочка до 36 месяцев
          </h1>
          <p className="mb-8 text-lg text-[var(--text-muted)]">
            Покупайте iPhone, смартфоны, ноутбуки и технику для дома в рассрочку
            0% от Халва. Быстрое оформление прямо на сайте.
          </p>
          <Link
            href="/catalog/gadzhety"
            className="inline-flex rounded-full bg-[#1a1a1a] px-8 py-3 font-medium text-white transition hover:bg-black"
          >
            Смотреть товары
          </Link>
        </div>
      </div>

      <div className="mt-8 overflow-hidden rounded-2xl border border-[var(--border)]">
        <img
          src="/images/original/promos/rassrochka.PNG"
          alt="Рассрочка 0%"
          className="w-full object-cover"
        />
      </div>

      <div className="mt-12 grid gap-6 md:grid-cols-3">
        <div className="rounded-xl border border-[var(--border)] bg-white p-6">
          <h3 className="mb-2 text-lg font-semibold">0% переплат</h3>
          <p className="text-sm text-[var(--text-muted)]">
            Никаких скрытых комиссий и процентов на весь срок рассрочки.
          </p>
        </div>
        <div className="rounded-xl border border-[var(--border)] bg-white p-6">
          <h3 className="mb-2 text-lg font-semibold">До 36 месяцев</h3>
          <p className="text-sm text-[var(--text-muted)]">
            Выбирайте удобный срок: 3, 6, 10, 12, 24 или 36 месяцев.
          </p>
        </div>
        <div className="rounded-xl border border-[var(--border)] bg-white p-6">
          <h3 className="mb-2 text-lg font-semibold">Онлайн-оформление</h3>
          <p className="text-sm text-[var(--text-muted)]">
            Решение за несколько минут без визита в офис.
          </p>
        </div>
      </div>
    </div>
  );
}
