import SocialLinks from "@/components/SocialLinks";

export const metadata = {
  title: "Контакты — GADGET·BAR",
  description: "Адреса магазинов, телефон и режим работы GADGET·BAR.",
};

export default function ContactsPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Контакты</h1>
      <div className="grid gap-8 lg:grid-cols-2">
        <div className="rounded-xl border border-[var(--border)] bg-white p-6">
          <h2 className="mb-4 text-xl font-semibold">Магазин в Воронеже</h2>
          <ul className="space-y-3 text-[var(--text-muted)]">
            <li>
              <span className="font-medium text-[#1a1a1a]">Адрес:</span> ул.
              Примерная, 123, ТЦ «Галерея»
            </li>
            <li>
              <span className="font-medium text-[#1a1a1a]">Телефон:</span>{" "}
              <a href="tel:88005051307" className="hover:text-[var(--accent)]">
                8 (800) 505-13-07
              </a>
            </li>
            <li>
              <span className="font-medium text-[#1a1a1a]">Режим работы:</span>{" "}
              ежедневно с 10:00 до 21:00
            </li>
            <li>
              <span className="font-medium text-[#1a1a1a]">Email:</span>{" "}
              <a
                href="mailto:info@gadget-bar.ru"
                className="hover:text-[var(--accent)]"
              >
                info@gadget-bar.ru
              </a>
            </li>
          </ul>
        </div>

        <div className="rounded-xl border border-[var(--border)] bg-white p-6">
          <h2 className="mb-4 text-xl font-semibold">Социальные сети</h2>
          <SocialLinks />
        </div>
      </div>
    </div>
  );
}
