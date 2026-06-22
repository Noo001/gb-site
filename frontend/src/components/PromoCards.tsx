import Link from "next/link";

const promos = [
  {
    title: "Бесплатная защита экрана в Gadget bar",
    subtitle: "При покупке смартфона",
    image: "/images/original/promo-screen.png",
    href: "/sales/besplatnaya-zashchita-ekrana-v-gadget-bar",
  },
  {
    title: "Скидка за отзыв",
    subtitle: "Бессрочная акция",
    image: "/images/original/promo-review.png",
    href: "/sales/skidka-za-otzyv",
  },
  {
    title: "Программа Trade-In",
    subtitle: "Выгодный обмен старого устройства",
    image: "/images/original/promo-tradein.png",
    href: "/sales/programma-trade-in",
  },
];

export default function PromoCards() {
  return (
    <section className="container-theme mb-12">
      <div className="mb-6 flex items-center justify-between">
        <h2 className="text-2xl font-semibold">Акции</h2>
        <Link
          href="/sales"
          className="flex items-center gap-1 text-sm font-medium text-[var(--text-muted)] hover:text-[var(--accent)]"
        >
          Смотреть все
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="m9 18 6-6-6-6" />
          </svg>
        </Link>
      </div>
      <div className="grid gap-5 md:grid-cols-3">
        {promos.map((p) => (
          <Link
            key={p.href}
            href={p.href}
            className="group block overflow-hidden rounded-2xl bg-white transition hover:shadow-lg"
          >
            <div className="relative aspect-[16/10] overflow-hidden bg-[var(--muted)]">
              <img
                src={p.image}
                alt={p.title}
                className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                loading="lazy"
              />
            </div>
            <div className="p-4">
              <h3 className="mb-1 text-base font-semibold">{p.title}</h3>
              <p className="text-sm text-[var(--text-muted)]">{p.subtitle}</p>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}
