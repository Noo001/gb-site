import Link from "next/link";
import type { Category } from "@/lib/api";

type MenuGroup = {
  title: string;
  path: string;
  catalogPath?: string;
};

const featuredGroups: MenuGroup[] = [
  { title: "Apple", path: "/brands/apple/", catalogPath: "/catalog/apple/" },
  { title: "Samsung", path: "/brands/samsung/", catalogPath: "/catalog/samsung/" },
  { title: "Dyson", path: "/brands/dyson/", catalogPath: "/catalog/dyson/" },
  { title: "Смартфоны", path: "/catalog/smartfony/" },
  { title: "Игровые консоли", path: "/catalog/igrovye-konsoli/" },
  { title: "Наушники и аудио", path: "/catalog/naushniki-i-audio/" },
  { title: "Видеотехника / Экшн-камеры", path: "/catalog/videotekhnika-ekshn-kamery/" },
  { title: "SMEG", path: "/catalog/smeg/" },
  { title: "Планшеты и ПК", path: "/catalog/planshety-i-pk/" },
  { title: "Аксессуары", path: "/catalog/aksessuary/" },
  { title: "Гаджеты", path: "/catalog/gadzhety/" },
];

function findCategory(categories: Category[], path: string): Category | undefined {
  for (const cat of categories) {
    if (cat.full_path === path || cat.url === path) return cat;
    if (cat.children?.length) {
      const found = findCategory(cat.children, path);
      if (found) return found;
    }
  }
  return undefined;
}

function CategoryThumb({ image, name }: { image?: string; name: string }) {
  return image ? (
    <img
      src={image}
      alt={name}
      className="h-10 w-10 rounded-md border border-[var(--border)] object-contain bg-white"
      loading="lazy"
    />
  ) : (
    <span className="flex h-10 w-10 items-center justify-center rounded-md border border-[var(--border)] bg-[var(--muted)] text-sm font-semibold text-[var(--text-muted)]">
      {name.charAt(0)}
    </span>
  );
}

export default function MegaMenu({ categories }: { categories: Category[] }) {
  return (
    <div className="group relative">
      <button className="inline-flex items-center gap-2 rounded-lg bg-[#1a1a1a] px-4 py-2.5 text-sm font-medium text-white hover:bg-black">
        <MenuIcon />
        Каталог
      </button>

      <div className="absolute left-0 top-full z-40 hidden pt-2 group-hover:block">
        <div className="w-[var(--max-width)] max-w-[calc(100vw-2rem)] rounded-2xl border border-[var(--border)] bg-white p-6 shadow-xl">
          <div className="flex gap-8">
            {/* Columns */}
            <div className="grid flex-1 grid-cols-2 gap-x-8 gap-y-6 md:grid-cols-3 lg:grid-cols-4">
              {featuredGroups.map((group) => {
                const cat =
                  findCategory(categories, group.path) ||
                  (group.catalogPath
                    ? findCategory(categories, group.catalogPath)
                    : undefined);

                if (!cat) return null;

                return (
                  <div key={group.path} className="min-w-0">
                    <Link
                      href={cat.url}
                      className="mb-3 flex items-center gap-3 font-semibold text-[#1a1a1a] hover:text-[var(--accent)]"
                    >
                      <CategoryThumb image={cat.image} name={group.title} />
                      <span className="truncate">{group.title}</span>
                    </Link>
                    {cat.children && cat.children.length > 0 && (
                      <ul className="space-y-1.5">
                        {cat.children.map((child) => (
                          <li key={child.id}>
                            <Link
                              href={child.url}
                              className="block text-sm text-[var(--text-muted)] transition hover:text-[var(--accent)]"
                            >
                              {child.name}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                );
              })}
            </div>

            {/* Promo banner */}
            <div className="hidden w-72 shrink-0 rounded-xl bg-gradient-to-br from-[#e0f7fa] to-[#b2ebf2] p-5 lg:block">
              <div className="mb-3 text-xs font-semibold uppercase tracking-wide text-[#00838f]">
                ХАЛВА | GADGET-BAR
              </div>
              <h3 className="mb-2 text-2xl font-bold leading-tight text-[#1a1a1a]">
                Рассрочка до 36 мес.
              </h3>
              <p className="mb-4 text-sm text-[var(--text-muted)]">
                Покупайте гаджеты в рассрочку 0% от Халва. Быстрое оформление
                прямо на сайте.
              </p>
              <Link
                href="/installment"
                className="inline-flex rounded-full bg-[#1a1a1a] px-5 py-2 text-sm font-medium text-white transition hover:bg-black"
              >
                Подробнее
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function MenuIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="18"
      height="18"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <line x1="4" x2="20" y1="6" y2="6" />
      <line x1="4" x2="20" y1="12" y2="12" />
      <line x1="4" x2="20" y1="18" y2="18" />
    </svg>
  );
}
