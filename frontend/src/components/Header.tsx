import Link from "next/link";
import { getCategories, type Category } from "@/lib/api";
import MegaMenu from "./MegaMenu";
import HeaderActions from "./HeaderActions";
import CitySelector from "./CitySelector";
import PhonePopover from "./PhonePopover";
import SearchBox from "./SearchBox";
import SocialLinks from "./SocialLinks";

function ChevronDownIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="16"
      height="16"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="m6 9 6 6 6-6" />
    </svg>
  );
}

export default async function Header() {
  const categories = await getCategories();
  const topLinks: (
    | { name: string; href: string }
    | { name: string; submenu: { name: string; href: string }[] }
  )[] = [
    { name: "Бренды", href: "/brands" },
    { name: "Статьи", href: "/blog" },
    { name: "Акции", href: "/sales" },
    { name: "Рассрочка", href: "/installment" },
    { name: "Контакты", href: "/contacts" },
    {
      name: "Информация",
      submenu: [
        { name: "Способы оплаты", href: "/info/payment" },
        { name: "Доставка", href: "/info/delivery" },
        { name: "Гарантия", href: "/info/warranty" },
        { name: "Обмен и возврат", href: "/info/return" },
      ],
    },
  ];

  return (
    <header className="bg-white">
      {/* Top bar */}
      <div className="bg-[var(--header-top-bg)] text-sm">
        <div className="container-theme flex h-11 items-center justify-between">
          <div className="flex items-center gap-6">
            <Link href="/" className="shrink-0">
              <img
                src="/images/original/logo.png"
                alt="GADGET·BAR"
                className="h-8 w-auto"
                width="3692"
                height="488"
              />
            </Link>
            <div className="hidden md:block">
              <CitySelector />
            </div>
          </div>

          <div className="hidden flex-1 px-8 lg:block">
            <SearchBox />
          </div>

          <div className="flex items-center gap-5">
            <div className="hidden md:block">
              <PhonePopover />
            </div>
            <SocialLinks className="relative z-10" />
          </div>
        </div>
      </div>

      {/* Main nav */}
      <div className="border-b border-[var(--border)]">
        <div className="container-theme flex h-14 items-center justify-between gap-4">
          <div className="flex items-center gap-4">
            <MegaMenu categories={categories} />
            <nav className="hidden items-center gap-6 md:flex">
              {topLinks.map((link) =>
                "submenu" in link ? (
                  <div key={link.name} className="group relative">
                    <button className="inline-flex items-center gap-1 text-sm text-[#1a1a1a] hover:text-[var(--accent)]">
                      {link.name}
                      <ChevronDownIcon className="h-3.5 w-3.5" />
                    </button>
                    <div className="absolute left-0 top-full z-30 hidden min-w-[200px] pt-2 group-hover:block">
                      <div className="rounded-xl border border-[var(--border)] bg-white py-2 shadow-xl">
                        {link.submenu.map((item) => (
                          <Link
                            key={item.href}
                            href={item.href}
                            className="block px-4 py-2 text-sm text-[var(--text-muted)] transition hover:bg-gray-50 hover:text-[var(--accent)]"
                          >
                            {item.name}
                          </Link>
                        ))}
                      </div>
                    </div>
                  </div>
                ) : (
                  <Link
                    key={link.name}
                    href={link.href}
                    className="text-sm text-[#1a1a1a] hover:text-[var(--accent)]"
                  >
                    {link.name}
                  </Link>
                )
              )}
            </nav>
          </div>

          <HeaderActions />
        </div>
      </div>
    </header>
  );
}
