"use client";

import { useCallback } from "react";
import useEmblaCarousel from "embla-carousel-react";
import Link from "next/link";
import type { Category } from "@/lib/api";

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

export default function BrandCarousel({ brands }: { brands: Category[] }) {
  if (brands.length === 0) return null;

  const [emblaRef, emblaApi] = useEmblaCarousel({
    loop: brands.length > 3,
    align: "start",
    slidesToScroll: 1,
    containScroll: false,
    dragFree: true,
  });

  const scrollPrev = useCallback(() => emblaApi?.scrollPrev(), [emblaApi]);
  const scrollNext = useCallback(() => emblaApi?.scrollNext(), [emblaApi]);

  return (
    <div className="relative">
      <div ref={emblaRef} className="overflow-hidden">
        <div className="flex">
          {brands.map((brand) => (
            <div
              key={brand.id}
              className="min-w-0 flex-[0_0_33.333%] px-2 sm:flex-[0_0_25%] md:flex-[0_0_20%] lg:flex-[0_0_16.666%] xl:flex-[0_0_14.285%]"
            >
              <Link
                href={brand.url}
                className="flex h-24 items-center justify-center rounded-xl border border-[var(--border)] bg-white px-4 transition hover:border-[var(--accent)] hover:shadow-sm"
              >
                {brand.image ? (
                  <img
                    src={brand.image}
                    alt={brand.name}
                    className="max-h-14 w-full object-contain"
                    loading="lazy"
                  />
                ) : (
                  <span className="text-center text-sm font-semibold text-[#1a1a1a]">
                    {brand.name}
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
