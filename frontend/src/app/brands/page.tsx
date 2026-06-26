import Link from "next/link";
import { getCategories } from "@/lib/api";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Бренды — GADGET·BAR",
  description: "Каталог брендов в GADGET·BAR: Apple, Samsung, Dyson, Xiaomi, Sony и другие.",
};

export default async function BrandsPage() {
  const categories = await getCategories();

  const brands = categories
    .filter((c) => c.full_path.startsWith("/brands/"))
    .sort((a, b) => a.name.localeCompare(b.name));

  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Бренды</h1>
      {brands.length === 0 ? (
        <p className="text-[var(--text-muted)]">Список брендов скоро появится.</p>
      ) : (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          {brands.map((brand) => (
            <Link
              key={brand.id}
              href={`/brands/${brand.slug}`}
              className="flex items-center gap-4 rounded-xl border border-[var(--border)] bg-white p-4 transition hover:border-[var(--accent)] hover:text-[var(--accent)]"
            >
              {brand.image ? (
                <img
                  src={brand.image}
                  alt={brand.name}
                  className="h-12 w-12 object-contain"
                  loading="lazy"
                />
              ) : (
                <span className="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--muted)] text-lg font-semibold text-[var(--text-muted)]">
                  {brand.name.charAt(0)}
                </span>
              )}
              <span className="font-medium">{brand.name}</span>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
