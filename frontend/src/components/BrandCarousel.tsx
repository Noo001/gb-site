"use client";

import { useCallback } from "react";
import useEmblaCarousel from "embla-carousel-react";
import Link from "next/link";

function AppleLogo() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-6 w-6">
      <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
    </svg>
  );
}

function XiaomiLogo() {
  return (
    <svg viewBox="0 0 48 48" fill="currentColor" className="h-6 w-6">
      <rect width="48" height="48" rx="8" fill="#FF6900" />
      <text x="50%" y="56%" dominantBaseline="middle" textAnchor="middle" fill="white" fontSize="22" fontWeight="bold">mi</text>
    </svg>
  );
}

function HuaweiLogo() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-6 w-6">
      <path d="M12 2c.5 1.5 1 3 1.5 4.5.5-1.5 1-3 1.5-4.5h3c-.5 1.5-1 3-1.5 4.5.5-1.5 1-3 1.5-4.5h3c-1 2.5-2 5-3 7.5 1 2 2 4 3 6h-3c-.5-1.5-1-3-1.5-4.5-.5 1.5-1 3-1.5 4.5h-3c-.5-1.5-1-3-1.5-4.5-.5 1.5-1 3-1.5 4.5H5c1-2 2-4 3-6-1-2.5-2-5-3-7.5h3c.5 1.5 1 3 1.5 4.5.5-1.5 1-3 1.5-4.5h3z" />
    </svg>
  );
}

const brands = [
  { name: "Apple", href: "/brands/apple", logo: <AppleLogo /> },
  { name: "SAMSUNG", href: "/brands/samsung", logo: null },
  { name: "Xiaomi", href: "/brands/xiaomi", logo: <XiaomiLogo /> },
  { name: "HONOR", href: "/brands/honor", logo: null },
  { name: "Huawei", href: "/brands/huawei", logo: <HuaweiLogo /> },
  { name: "SONY", href: "/brands/sony", logo: null },
  { name: "dyson", href: "/brands/dyson", logo: null },
  { name: "smeg", href: "/brands/smeg", logo: null },
  { name: "JBL", href: "/brands/jbl", logo: null },
  { name: "DJI", href: "/brands/dji", logo: null },
];

function PrevIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m15 18-6-6 6-6" />
    </svg>
  );
}

function NextIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m9 18 6-6-6-6" />
    </svg>
  );
}

export default function BrandCarousel() {
  const [emblaRef, emblaApi] = useEmblaCarousel({
    loop: true,
    align: "start",
    slidesToScroll: 1,
    containScroll: false,
  });

  const scrollPrev = useCallback(() => emblaApi?.scrollPrev(), [emblaApi]);
  const scrollNext = useCallback(() => emblaApi?.scrollNext(), [emblaApi]);

  return (
    <div className="relative">
      <div ref={emblaRef} className="overflow-hidden">
        <div className="flex">
          {brands.map((b) => (
            <div key={b.name} className="min-w-0 flex-[0_0_16.666%] px-3 sm:flex-[0_0_20%] md:flex-[0_0_16.666%]">
              <Link
                href={b.href}
                className="flex h-20 items-center justify-center rounded-xl border border-transparent transition hover:border-[var(--border)] hover:bg-gray-50"
              >
                {b.logo ? (
                  <div className="flex items-center gap-2 text-[#1a1a1a]">
                    {b.logo}
                    <span className="text-sm font-semibold">{b.name}</span>
                  </div>
                ) : (
                  <span className="text-lg font-bold tracking-wide text-[#1a1a1a]">
                    {b.name}
                  </span>
                )}
              </Link>
            </div>
          ))}
        </div>
      </div>

      <button
        onClick={scrollPrev}
        className="absolute -left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-[var(--border)] bg-white text-[var(--text-muted)] shadow-sm transition hover:text-[#1a1a1a]"
        aria-label="Назад"
      >
        <PrevIcon />
      </button>
      <button
        onClick={scrollNext}
        className="absolute -right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-[var(--border)] bg-white text-[var(--text-muted)] shadow-sm transition hover:text-[#1a1a1a]"
        aria-label="Вперёд"
      >
        <NextIcon />
      </button>
    </div>
  );
}
