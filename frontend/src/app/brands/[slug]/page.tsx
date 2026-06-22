import { notFound } from "next/navigation";
import { getCategories, getProducts } from "@/lib/api";
import ProductCard from "@/components/ProductCard";
import Breadcrumbs from "@/components/Breadcrumbs";

export const dynamic = "force-dynamic";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props) {
  const { slug } = await params;
  const categories = await getCategories();
  const brand = categories.find((c) => c.slug === slug && c.full_path.startsWith("/brands/"));

  return {
    title: brand ? `${brand.name} — GADGET·BAR` : "Бренд",
  };
}

export default async function BrandPage({ params }: Props) {
  const { slug } = await params;
  const categories = await getCategories();
  const brand = categories.find((c) => c.slug === slug && c.full_path.startsWith("/brands/"));

  if (!brand) {
    notFound();
  }

  const products = await getProducts({ brand: brand.name, per_page: "24" });

  const breadcrumbs = [
    { name: "Главная", url: "/" },
    { name: "Бренды", url: "/brands" },
    { name: brand.name, url: brand.url },
  ];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />
      <h1 className="mb-2 text-3xl font-semibold">
        {brand.name}
        <span className="ml-3 text-base font-normal text-[var(--text-muted)]">
          {products.total} товаров
        </span>
      </h1>

      {products.data.length === 0 ? (
        <p className="mt-6 text-[var(--text-muted)]">
          Товары этого бренда пока не добавлены.
        </p>
      ) : (
        <div className="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          {products.data.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
}
