"use client";

import { useState } from "react";
import { useCityStore } from "@/stores/cityStore";
import SocialLinks from "./SocialLinks";

const storesByCity: Record<string, { address: string; hours: string }[]> = {
  Воронеж: [
    { address: "проспект Революции, 46", hours: "10:00 – 22:00" },
    { address: "ул. Плехановская, 22", hours: "10:00 – 22:00" },
    { address: "ТК «Воронежский», 2 этаж", hours: "10:00 – 21:00" },
  ],
  Москва: [
    { address: "ул. Тверская, 12", hours: "10:00 – 22:00" },
    { address: "ТЦ «Европейский»", hours: "10:00 – 22:00" },
  ],
  "Ростов-на-Дону": [
    { address: "ул. Большая Садовая, 100", hours: "10:00 – 21:00" },
  ],
};

function PhoneIcon({ className }: { className?: string }) {
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
      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
    </svg>
  );
}

export default function PhonePopover() {
  const [open, setOpen] = useState(false);
  const city = useCityStore((s) => s.city);
  const stores = storesByCity[city] || storesByCity["Воронеж"];

  return (
    <div className="relative">
      <button
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-1.5 font-medium hover:text-[var(--accent)]"
      >
        <PhoneIcon />
        8 (800) 505-13-07
      </button>

      {open && (
        <>
          <div
            className="fixed inset-0 z-30"
            onClick={() => setOpen(false)}
          />
          <div className="absolute right-0 top-full z-40 mt-2 w-80 rounded-2xl border border-[var(--border)] bg-white p-5 shadow-xl">
            <a
              href="tel:88005051307"
              className="block text-lg font-semibold text-[#1a1a1a] hover:text-[var(--accent)]"
            >
              8 (800) 505-13-07
            </a>
            <p className="mb-4 text-sm text-[var(--text-muted)]">Отдел продаж</p>
            <button className="mb-5 w-full rounded-lg bg-[#1a1a1a] py-2.5 text-sm font-medium text-white transition hover:bg-black">
              Заказать звонок
            </button>

            <div className="mb-3 text-sm font-semibold">Режим работы</div>
            <ul className="mb-5 space-y-3 text-sm">
              {stores.map((s) => (
                <li key={s.address}>
                  <div className="text-[#1a1a1a]">{s.address}</div>
                  <div className="text-[var(--text-muted)]">{s.hours}</div>
                </li>
              ))}
            </ul>

            <SocialLinks />
          </div>
        </>
      )}
    </div>
  );
}
