import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { getProduct, getSeo } from "@/lib/api";
import Breadcrumbs from "@/components/Breadcrumbs";
import ProductTabs from "@/components/ProductTabs";
import AddToCartButton from "@/components/AddToCartButton";
import ComingSoon from "@/components/ComingSoon";
import WishlistButton from "@/components/WishlistButton";

export const dynamic = "force-dynamic";

function HeartIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="22"
      height="22"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
    </svg>
  );
}

function CompareIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="22"
      height="22"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M3 6h18M3 12h18M3 18h18" />
    </svg>
  );
}

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const [product, seo] = await Promise.all([
    getProduct(slug),
    getSeo(`/product/${slug}/`),
  ]);

  if (!product) {
    return { title: "Страница не найдена" };
  }

  return {
    title: seo.title ?? product.name,
    description: seo.description ?? undefined,
  };
}

export default async function ProductPage({ params }: Props) {
  const { slug } = await params;
  const [product, seo] = await Promise.all([
    getProduct(slug),
    getSeo(`/product/${slug}/`),
  ]);

  if (!product) {
    notFound();
  }

  const breadcrumbs = seo.breadcrumbs?.length
    ? seo.breadcrumbs
    : [
        { name: "Главная", url: "/" },
        { name: product.category?.name ?? "Каталог", url: product.category?.url ?? "/catalog" },
        { name: product.name, url: product.url },
      ];

  const fallbackImage = product.category?.image || "/images/placeholder-product.svg";
  const thumbs = product.images?.length ? product.images : [fallbackImage];
  const mainImage = thumbs[0];

  return (
    <div className="container-theme pb-12">
      <Breadcrumbs items={breadcrumbs} />

      <div className="mt-4 grid gap-8 lg:grid-cols-2">
        {/* Gallery */}
        <div className="flex gap-4">
          {thumbs.length > 0 && (
            <div className="flex flex-col gap-3">
              {thumbs.slice(0, 4).map((img, i) => (
                <div
                  key={img + i}
                  className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg border border-[var(--border)] bg-white"
                >
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={img}
                    alt=""
                    className="h-full w-full object-contain p-1"
                  />
                </div>
              ))}
            </div>
          )}
          <div className="flex flex-1 items-center justify-center overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--muted)] p-8">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={mainImage}
              alt={product.name}
              className="max-h-[420px] w-full object-contain"
            />
          </div>
        </div>

        {/* Info */}
        <div>
          <div className="mb-4 flex items-start justify-between gap-4">
            <h1 className="text-3xl font-semibold leading-tight">
              {seo.h1 ?? product.name}
            </h1>
            <div className="flex gap-2">
              <WishlistButton productId={product.id} />
              <ComingSoon
                className="flex h-10 w-10 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--text-muted)] hover:text-[var(--accent)]"
                label="Сравнение товаров скоро появится"
                aria-label="Сравнить"
              >
                <CompareIcon />
              </ComingSoon>
            </div>
          </div>

          <div className="mb-6 rounded-xl border border-[var(--border)] bg-white p-5">
            <div className="mb-4 text-3xl font-bold">Цена по запросу</div>
            <div className="mb-3">
              <AddToCartButton productId={product.id} fullWidth />
            </div>
            <ComingSoon
              className="w-full rounded-lg border border-[var(--border)] bg-[var(--muted)] py-3 font-medium transition hover:border-[var(--accent)]"
              label="Оформление в 1 клик скоро появится"
            >
              Оформить в 1 клик
            </ComingSoon>
          </div>

          {product.offers && product.offers.length > 0 && (
            <div className="mb-6">
              <h2 className="mb-3 font-semibold">Предложения</h2>
              <ul className="space-y-2">
                {product.offers.map((offer) => (
                  <li
                    key={offer.id}
                    className="flex items-center justify-between rounded-lg border border-[var(--border)] p-3"
                  >
                    <span className="text-sm">{offer.name}</span>
                    <span className="text-xs text-[var(--text-muted)]">
                      {offer.sku}
                    </span>
                  </li>
                ))}
              </ul>
            </div>
          )}

          {product.category && (
            <div className="flex flex-wrap gap-3">
              <Link
                href={product.category.url}
                className="rounded-lg border border-[var(--border)] px-4 py-2 text-sm hover:border-[var(--accent)]"
              >
                Все товары {product.category.name}
              </Link>
            </div>
          )}
        </div>
      </div>

      <ProductTabs productName={product.name} description={product.description} />
    </div>
  );
}
