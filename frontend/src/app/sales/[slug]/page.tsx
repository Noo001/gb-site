import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { sales } from "@/lib/sales";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const sale = sales.find((s) => s.slug === slug);
  return {
    title: sale ? `${sale.title} — Акции GADGET·BAR` : "Акция не найдена",
  };
}

export default async function SalePage({ params }: Props) {
  const { slug } = await params;
  const sale = sales.find((s) => s.slug === slug);
  if (!sale) {
    notFound();
  }

  return (
    <div className="container-theme pb-12">
      <div className="mb-6 text-sm text-[var(--text-muted)]">
        <Link href="/sales" className="hover:text-[var(--accent)]">
          ← Все акции
        </Link>
      </div>
      <div className="overflow-hidden rounded-2xl border border-[var(--border)] bg-white">
        <img
          src={sale.image}
          alt={sale.title}
          className="h-64 w-full object-cover md:h-96"
        />
        <div className="p-6 md:p-10">
          <h1 className="mb-6 text-3xl font-semibold">{sale.title}</h1>
          <p className="max-w-3xl text-lg leading-relaxed text-[var(--text-muted)]">
            {sale.description}
          </p>
        </div>
      </div>
    </div>
  );
}
