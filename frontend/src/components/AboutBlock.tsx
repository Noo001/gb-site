"use client";

import Link from "next/link";
import { useCityStore } from "@/stores/cityStore";

export default function AboutBlock() {
  const city = useCityStore((s) => s.city);

  return (
    <section className="container-theme mb-12">
      <div className="overflow-hidden rounded-2xl border border-[var(--border)] bg-white">
        <div className="grid md:grid-cols-2">
          <div className="p-8 md:p-12">
            <h2 className="mb-6 text-2xl font-semibold md:text-3xl">
              Интернет-магазин в {city}
            </h2>
            <div className="mb-8 space-y-4 text-[var(--text-muted)]">
              <p>
                Gadget Bar — это один из крупнейших розничных продавцов оригинальной техники в 15 регионах России. Мы работаем на рынке с 2012 года и предлагаем широкий ассортимент устройств от ведущих брендов: Apple, Xiaomi, Samsung, Dyson и других.
              </p>
              <p>
                В наших магазинах вы найдёте всё — от смартфонов и игровых приставок до фенов, очистителей воздуха и аксессуаров.
              </p>
            </div>
            <Link
              href="/company"
              className="inline-block rounded-lg bg-[#1a1a1a] px-6 py-3 text-sm font-medium text-white transition hover:bg-black"
            >
              Подробнее
            </Link>
          </div>
          <div className="relative min-h-[280px] bg-[var(--muted)] md:min-h-full">
            <img
              src="/images/original/about-collage.png"
              alt="Gadget Bar"
              className="h-full w-full object-cover"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </section>
  );
}
