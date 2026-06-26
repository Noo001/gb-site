import Link from "next/link";
import { sales } from "@/lib/sales";

export const metadata = {
  title: "Акции — GADGET·BAR",
  description: "Актуальные акции, скидки и спецпредложения GADGET·BAR.",
};

export default function SalesPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Акции</h1>
      <div className="grid gap-5 md:grid-cols-2">
        {sales.map((sale) => (
          <div
            key={sale.slug}
            className="overflow-hidden rounded-xl border border-[var(--border)] bg-white transition hover:border-[var(--accent)]"
          >
            <img
              src={sale.image}
              alt={sale.title}
              className="h-56 w-full object-cover"
            />
            <div className="p-6">
              <h2 className="mb-3 text-xl font-semibold">{sale.title}</h2>
              <p className="mb-5 text-[var(--text-muted)]">{sale.description}</p>
              <Link
                href={`/sales/${sale.slug}`}
                className="inline-flex rounded-full bg-[#1a1a1a] px-5 py-2 text-sm font-medium text-white transition hover:bg-black"
              >
                Подробнее
              </Link>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
