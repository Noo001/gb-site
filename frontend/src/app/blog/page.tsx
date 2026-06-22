import Link from "next/link";
import { articles } from "@/lib/articles";

export const metadata = {
  title: "Статьи — GADGET·BAR",
  description: "Обзоры гаджетов, советы по выбору и новости техники.",
};

export default function BlogPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Статьи</h1>
      <div className="grid gap-5 md:grid-cols-2">
        {articles.map((article) => (
          <article
            key={article.slug}
            className="overflow-hidden rounded-xl border border-[var(--border)] bg-white transition hover:border-[var(--accent)]"
          >
            <img
              src={article.image}
              alt={article.title}
              className="h-52 w-full object-cover"
            />
            <div className="p-6">
              <h2 className="mb-3 text-xl font-semibold">
                <Link
                  href={`/blog/${article.slug}`}
                  className="hover:text-[var(--accent)]"
                >
                  {article.title}
                </Link>
              </h2>
              <p className="mb-4 text-sm text-[var(--text-muted)]">
                {article.excerpt}
              </p>
              <Link
                href={`/blog/${article.slug}`}
                className="text-sm font-medium text-[var(--accent)] hover:text-[var(--accent-hover)]"
              >
                Читать дальше →
              </Link>
            </div>
          </article>
        ))}
      </div>
    </div>
  );
}
