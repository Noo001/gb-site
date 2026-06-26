"use client";

import { useEffect, useState } from "react";
import { useCityStore } from "@/stores/cityStore";

const allCities = [
  "Москва",
  "Санкт-Петербург",
  "Воронеж",
  "Липецк",
  "Белгород",
  "Краснодар",
  "Старый Оскол",
  "Тамбов",
  "Пермь",
  "Нижний Новгород",
  "Уфа",
  "Ижевск",
  "Казань",
  "Калуга",
  "Ростов-на-Дону",
  "Ярославль",
];

const popular = ["Москва", "Тамбов", "Краснодар", "Ярославль"];

function LocationIcon({ className }: { className?: string }) {
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
      <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
      <circle cx="12" cy="10" r="3" />
    </svg>
  );
}

export default function CitySelector() {
  const { city, setCity, detected, setDetected } = useCityStore();
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");

  useEffect(() => {
    if (detected) return;

    fetch("/api/city/detect")
      .then((res) => res.json())
      .then((data) => {
        if (data.city && data.city !== city) {
          setCity(data.city);
        }
        setDetected(true);
      })
      .catch(() => setDetected(true));
  }, [detected, city, setCity, setDetected]);

  const filtered = query
    ? allCities.filter((c) => c.toLowerCase().includes(query.toLowerCase()))
    : allCities;

  const select = (c: string) => {
    setCity(c);
    setOpen(false);
    setQuery("");
  };

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        className="flex items-center gap-1 text-[var(--text-muted)] transition hover:text-[#1a1a1a] md:flex"
      >
        <LocationIcon />
        <span>{city}</span>
      </button>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <div className="mb-5 flex items-center justify-between">
              <h2 className="text-xl font-semibold">Выберите город</h2>
              <button
                onClick={() => setOpen(false)}
                className="text-2xl text-[var(--text-muted)] hover:text-[#1a1a1a]"
              >
                ×
              </button>
            </div>

            <input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Введите название города"
              className="mb-4 w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none focus:border-[var(--accent)]"
            />

            {!query && (
              <div className="mb-5 flex flex-wrap gap-2">
                {popular.map((c) => (
                  <button
                    key={c}
                    onClick={() => select(c)}
                    className="rounded-full bg-gray-100 px-3 py-1 text-sm transition hover:bg-gray-200"
                  >
                    {c}
                  </button>
                ))}
              </div>
            )}

            <div className="grid max-h-72 grid-cols-3 gap-2 overflow-y-auto text-sm">
              {filtered.map((c) => (
                <button
                  key={c}
                  onClick={() => select(c)}
                  className={`rounded-lg px-3 py-2 text-left transition ${
                    c === city
                      ? "bg-gray-100 font-medium"
                      : "hover:bg-gray-50"
                  }`}
                >
                  {c}
                </button>
              ))}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
