"use client";

import { useCallback, useEffect, useState } from "react";
import useEmblaCarousel from "embla-carousel-react";
import Image from "next/image";
import Link from "next/link";

const slides = [
  { image: "/images/original/banner-1.png", href: "/sales/kodovyy-zamok-udachi", alt: "Кодовый замок удачи" },
  { image: "/images/original/banner-2.png", href: "/catalog/smartfony", alt: "iPhone" },
  { image: "/images/original/banner-3.png", href: "/installment", alt: "Рассрочка" },
  { image: "/images/original/banner-4.png", href: "/brands/samsung", alt: "Samsung" },
  { image: "/images/original/banner-5.png", href: "/catalog/smartfony", alt: "iPhone" },
  { image: "/images/original/banner-6.png", href: "/brands/dyson", alt: "Dyson" },
  { image: "/images/original/banner-7.png", href: "/catalog/igrovye-konsoli", alt: "Игровые консоли" },
  { image: "/images/original/banner-8.png", href: "/catalog/naushniki-i-audio/kolonki", alt: "Яндекс Станции" },
  { image: "/images/original/banner-9.png", href: "/sales/trade-in", alt: "Trade-In" },
  { image: "/images/original/banner-10.png", href: "/brands/smeg", alt: "Smeg" },
];

function PrevIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m15 18-6-6 6-6" />
    </svg>
  );
}

function NextIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="m9 18 6-6-6-6" />
    </svg>
  );
}

export default function PromoCarousel() {
  const [emblaRef, emblaApi] = useEmblaCarousel({ loop: true });
  const [selected, setSelected] = useState(0);

  const scrollPrev = useCallback(() => emblaApi?.scrollPrev(), [emblaApi]);
  const scrollNext = useCallback(() => emblaApi?.scrollNext(), [emblaApi]);
  const scrollTo = useCallback((i: number) => emblaApi?.scrollTo(i), [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;
    const onSelect = () => setSelected(emblaApi.selectedScrollSnap());
    emblaApi.on("select", onSelect);
    onSelect();
    const autoplay = setInterval(() => emblaApi.scrollNext(), 5000);
    return () => {
      emblaApi.off("select", onSelect);
      clearInterval(autoplay);
    };
  }, [emblaApi]);

  return (
    <div className="relative overflow-hidden rounded-2xl">
      <div ref={emblaRef} className="overflow-hidden">
        <div className="flex">
          {slides.map((s) => (
            <div key={s.href + s.image} className="relative min-w-0 flex-[0_0_100%]">
              <Link href={s.href} className="block">
                <div className="relative aspect-[3/1] w-full">
                  <Image
                    src={s.image}
                    alt={s.alt}
                    fill
                    className="object-cover"
                    priority
                  />
                </div>
              </Link>
            </div>
          ))}
        </div>
      </div>

      <button
        onClick={scrollPrev}
        className="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-[#1a1a1a] shadow transition hover:bg-white"
        aria-label="Назад"
      >
        <PrevIcon />
      </button>
      <button
        onClick={scrollNext}
        className="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-[#1a1a1a] shadow transition hover:bg-white"
        aria-label="Вперёд"
      >
        <NextIcon />
      </button>

      <div className="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
        {slides.map((_, i) => (
          <button
            key={i}
            onClick={() => scrollTo(i)}
            className={`h-2 w-2 rounded-full transition ${
              i === selected ? "bg-[#1a1a1a]" : "bg-white/70"
            }`}
            aria-label={`Слайд ${i + 1}`}
          />
        ))}
      </div>
    </div>
  );
}
