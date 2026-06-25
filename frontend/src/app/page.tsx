import Link from "next/link";
import { getCategories, getProducts } from "@/lib/api";
import ProductCard from "@/components/ProductCard";
import PromoCarousel from "@/components/PromoCarousel";
import BrandCarousel from "@/components/BrandCarousel";
import PromoCards from "@/components/PromoCards";
import AboutBlock from "@/components/AboutBlock";
import VkPosts from "@/components/VkPosts";
import Features from "@/components/Features";

export const dynamic = "force-dynamic";

export default async function HomePage() {
  const [categories, products] = await Promise.all([
    getCategories(),
    getProducts({ per_page: "12", has_image: "1" }),
  ]);

  return (
    <div className="pb-12">
      {/* Promo carousel */}
      <section className="container-theme my-6">
        <PromoCarousel />
      </section>

      {/* Brand carousel */}
      <section className="container-theme mb-10">
        <BrandCarousel />
      </section>

      {/* Best offers */}
      <section className="container-theme mb-12">
        <div className="mb-6 flex items-center justify-between">
          <h2 className="text-2xl font-semibold">Лучшие предложения</h2>
          <Link
            href="/catalog/gadzhety"
            className="text-sm font-medium text-[var(--accent)] hover:text-[var(--accent-hover)]"
          >
            Смотреть все →
          </Link>
        </div>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
          {products.data.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </section>

      {/* Categories */}
      <section className="container-theme mb-12">
        <h2 className="mb-6 text-2xl font-semibold">Каталог</h2>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          {categories
            .filter((category) => category.children && category.children.length > 0)
            .map((category) => (
            <Link
              key={category.id}
              href={category.url}
              className="flex flex-col items-center rounded-xl border border-[var(--border)] bg-white p-5 text-center font-medium transition hover:border-[var(--accent)] hover:text-[var(--accent)]"
            >
              {category.image ? (
                <img
                  src={category.image}
                  alt={category.name}
                  className="mb-3 h-16 w-16 object-contain"
                  loading="lazy"
                />
              ) : (
                <div className="mb-3 flex h-16 w-16 items-center justify-center rounded-lg bg-[var(--muted)] text-2xl font-semibold text-[var(--text-muted)]">
                  {category.name.charAt(0)}
                </div>
              )}
              <span className="line-clamp-2 text-sm">{category.name}</span>
            </Link>
          ))}
        </div>
      </section>

      {/* Promo cards */}
      <PromoCards />

      {/* About */}
      <AboutBlock />

      {/* VK posts */}
      <VkPosts />

      {/* Features */}
      <Features />
    </div>
  );
}
