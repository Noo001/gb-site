import Link from "next/link";
import SocialLinks from "./SocialLinks";

const columns = [
  {
    title: "Интернет-магазин",
    links: [
      { name: "Каталог", href: "/catalog" },
      { name: "Акции", href: "/sales" },
      { name: "Бренды", href: "/brands" },
    ],
  },
  {
    title: "Компания",
    links: [
      { name: "Магазины", href: "/stores" },
      { name: "Оставить отзыв", href: "/review" },
      { name: "О компании", href: "/company" },
    ],
  },
  {
    title: "Информация",
    links: [
      { name: "Способы оплаты", href: "/payment" },
      { name: "Доставка", href: "/delivery" },
      { name: "Гарантия", href: "/warranty" },
      { name: "Программа Trade-in", href: "/trade-in" },
    ],
  },
];

export default function Footer() {
  return (
    <footer className="border-t border-[var(--border)] bg-white">
      <div className="container-theme py-10">
        <p className="mb-10 text-sm leading-relaxed text-[var(--text-muted)]">
          Гарантия на товар и гарантийное обслуживание предоставляется в соответствии с Гражданским кодексом Российской Федерации (ГК РФ) и Законом Российской Федерации от 7 февраля 1992 г. № 2300-1 &quot;О защите прав потребителей&quot;, который является основным нормативно-правовым актом, регулирующим права потребителей, включая гарантийные сроки, порядок возврата и обмена товаров, ответственность продавца и изготовителя.
        </p>

        <div className="grid gap-8 md:grid-cols-4">
          {columns.map((col) => (
            <div key={col.title}>
              <h3 className="mb-4 font-semibold">{col.title}</h3>
              <ul className="space-y-2 text-sm text-[var(--text-muted)]">
                {col.links.map((link) => (
                  <li key={link.name}>
                    <Link href={link.href} className="hover:text-[var(--accent)]">
                      {link.name}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
          <div>
            <h3 className="mb-4 font-semibold">Контакты</h3>
            <a
              href="tel:88005051307"
              className="mb-3 block text-sm font-medium hover:text-[var(--accent)]"
            >
              8 (800) 505-13-07
            </a>
            <SocialLinks />
          </div>
        </div>
      </div>

      <div className="border-t border-[var(--border)]">
        <div className="container-theme flex flex-col items-center justify-between gap-2 py-6 text-sm text-[var(--text-muted)] md:flex-row">
          <p>© {new Date().getFullYear()} Gadget-Bar.ru</p>
          <div className="flex gap-4">
            <Link href="/privacy" className="hover:text-[var(--accent)]">
              Конфиденциальность
            </Link>
            <Link href="/offer" className="hover:text-[var(--accent)]">
              Оферта
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
