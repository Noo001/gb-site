import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { articles } from "@/lib/articles";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const article = articles.find((a) => a.slug === slug);
  return {
    title: article ? `${article.title} — Блог GADGET·BAR` : "Статья не найдена",
  };
}

export default async function BlogArticlePage({ params }: Props) {
  const { slug } = await params;
  const article = articles.find((a) => a.slug === slug);
  if (!article) {
    notFound();
  }

  return (
    <article className="container-theme pb-12">
      <div className="mb-6 text-sm text-[var(--text-muted)]">
        <Link href="/blog" className="hover:text-[var(--accent)]">
          ← Все статьи
        </Link>
      </div>
      <div className="overflow-hidden rounded-2xl border border-[var(--border)] bg-white">
        <img
          src={article.image}
          alt={article.title}
          className="h-64 w-full object-cover md:h-96"
        />
        <div className="p-6 md:p-10">
          <h1 className="mb-6 text-3xl font-semibold">{article.title}</h1>
          <p className="max-w-3xl text-lg leading-relaxed text-[var(--text-muted)]">
            {article.content}
          </p>
        </div>
      </div>
    </article>
  );
}
