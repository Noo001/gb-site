import Link from "next/link";

const posts = [
  {
    date: "18.06.2026",
    title: "Apple может поднять цены на iPhone, MacBook и другую технику",
    excerpt: "Об этом заявил...",
    image: "/images/original/promo-review.png",
    href: "#",
  },
  {
    date: "18.06.2026",
    title: "Оформляй подписку ChatGPT Plus или Claude в Gadget Bar",
    excerpt: "Подписка на ваш аккаунт...",
    image: "/images/original/promo-tradein.png",
    href: "#",
  },
  {
    date: "18.06.2026",
    title: "Нейросети всё больше становятся частью нашей жизни",
    excerpt: "С их помощью...",
    image: "/images/original/promo-screen.png",
    href: "#",
  },
  {
    date: "17.06.2026",
    title: "Как вернуть уведомления в Макс?",
    excerpt: "После удаления Макс из App Store у многих...",
    image: "/images/original/about-collage.png",
    href: "#",
  },
];

function VkIcon({ className }: { className?: string }) {
  return (
    <svg className={className} viewBox="0 0 24 24" fill="currentColor">
      <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.12-5.339-3.202C4.624 10.857 4 8.673 4 8.2c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.779.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.745-.576.745z" />
    </svg>
  );
}

export default function VkPosts() {
  return (
    <section className="container-theme mb-12">
      <div className="mb-6 flex items-center justify-between">
        <h2 className="text-2xl font-semibold">Мы ВКонтакте</h2>
        <Link
          href="https://vk.com/gadgetbarru"
          target="_blank"
          rel="noopener noreferrer"
          className="flex items-center gap-1 text-sm font-medium text-[var(--text-muted)] hover:text-[var(--accent)]"
        >
          Смотреть все
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="m9 18 6-6-6-6" />
          </svg>
        </Link>
      </div>
      <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {posts.map((post, idx) => (
          <Link
            key={idx}
            href={post.href}
            className="group block overflow-hidden rounded-2xl border border-[var(--border)] bg-white transition hover:shadow-lg"
          >
            <div className="relative aspect-[16/10] overflow-hidden bg-[var(--muted)]">
              <img
                src={post.image}
                alt={post.title}
                className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                loading="lazy"
              />
            </div>
            <div className="p-4">
              <div className="mb-2 flex items-center gap-2 text-xs text-[var(--text-muted)]">
                <VkIcon className="h-4 w-4 text-[#0077ff]" />
                {post.date}
              </div>
              <h3 className="line-clamp-2 text-sm font-semibold leading-snug">{post.title}</h3>
              <p className="mt-1 line-clamp-1 text-xs text-[var(--text-muted)]">{post.excerpt}</p>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}
