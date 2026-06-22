import type { Metadata } from "next";
import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import {
  getCategory,
  getCategoryProducts,
  getSeo,
} from "@/lib/api";
import Breadcrumbs from "@/components/Breadcrumbs";
import ProductCard from "@/components/ProductCard";

export const dynamic = "force-dynamic";

type Props = {
  params: Promise<{ path?: string[] }>;
  searchParams: Promise<{ page?: string }>;
};

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { path = [] } = await params;
  const slugPath = path.join("/");

  if (!slugPath) {
    notFound();
  }

  const [category, seo] = await Promise.all([
    getCategory(slugPath),
    getSeo(`/catalog/${slugPath}/`),
  ]);

  if (!category) {
    return { title: "Страница не найдена" };
  }

  return {
    title: seo.title ?? category.name,
    description: seo.description ?? undefined,
  };
}

export default async function CategoryPage({ params, searchParams }: Props) {
  const { path = [] } = await params;
  const { page = "1" } = await searchParams;
  const slugPath = path.join("/");

  if (!slugPath) {
    redirect("/catalog/gadzhety");
  }

  const category = await getCategory(slugPath);
  if (!category) {
    notFound();
  }

  const [products, seo] = await Promise.all([
    getCategoryProducts(slugPath, parseInt(page, 10)),
    getSeo(`/catalog/${slugPath}/`),
  ]);

  const breadcrumbs = seo.breadcrumbs?.length
    ? seo.breadcrumbs
    : [
        { name: "Главная", url: "/" },
        { name: category.name, url: category.url },
      ];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />

      <h1 className="mb-2 text-3xl font-semibold">
        {seo.h1 ?? category.name}
        <span className="ml-3 text-base font-normal text-[var(--text-muted)]">
          {products.total} товаров
        </span>
      </h1>

      {/* Subcategories */}
      {category.children.length > 0 && (
        <div className="mb-8 mt-6 flex flex-wrap gap-3">
          {category.children.map((child) => (
            <Link
              key={child.id}
              href={child.url}
              className="rounded-lg border border-[var(--border)] bg-white px-5 py-2.5 text-sm transition hover:border-[var(--accent)] hover:text-[var(--accent)]"
            >
              {child.name}
            </Link>
          ))}
        </div>
      )}

      <div className="flex flex-col gap-8 lg:flex-row">
        {/* Sidebar */}
        <aside className="w-full shrink-0 lg:w-56">
          <div className="rounded-xl border border-[var(--border)] bg-white p-4">
            <h3 className="mb-4 font-semibold">Категория</h3>
            <ul className="space-y-2 text-sm">
              {category.children.length > 0 ? (
                category.children.map((child) => (
                  <li key={child.id}>
                    <Link
                      href={child.url}
                      className="text-[var(--text-muted)] hover:text-[var(--accent)]"
                    >
                      {child.name}
                    </Link>
                  </li>
                ))
              ) : category.parent ? (
                <li>
                  <Link
                    href={category.parent.url}
                    className="text-[var(--text-muted)] hover:text-[var(--accent)]"
                  >
                    ← {category.parent.name}
                  </Link>
                </li>
              ) : null}
            </ul>
          </div>
        </aside>

        {/* Products grid */}
        <div className="flex-1">
          <div className="mb-5 flex items-center justify-between">
            <div className="text-sm text-[var(--text-muted)]">
              Сначала дешевые
            </div>
            <div className="flex gap-1">
              <span className="rounded-md border border-[var(--border)] p-1.5">
                ⊞
              </span>
            </div>
          </div>

          {products.data.length === 0 ? (
            <p className="text-[var(--text-muted)]">
              В этой категории пока нет товаров.
            </p>
          ) : (
            <>
              <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {products.data.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>
              <Pagination products={products} />
            </>
          )}
        </div>
      </div>
    </div>
  );
}

function Pagination({
  products,
}: {
  products: { current_page: number; last_page: number; path: string };
}) {
  if (products.last_page <= 1) return null;

  return (
    <div className="mt-8 flex items-center justify-center gap-2">
      {Array.from({ length: products.last_page }, (_, i) => i + 1).map((p) => (
        <Link
          key={p}
          href={`?page=${p}`}
          className={`rounded-lg px-3.5 py-1.5 text-sm border transition ${
            p === products.current_page
              ? "bg-[#1a1a1a] text-white border-[#1a1a1a]"
              : "border-[var(--border)] hover:border-[var(--accent)]"
          }`}
        >
          {p}
        </Link>
      ))}
    </div>
  );
}
