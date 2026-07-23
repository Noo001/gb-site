"use client";

import { useState } from "react";
import { toast } from "sonner";

function SearchIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="20"
      height="20"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <circle cx="11" cy="11" r="8" />
      <path d="m21 21-4.3-4.3" />
    </svg>
  );
}

export default function SearchBox() {
  const [query, setQuery] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!query.trim()) return;
    toast.info("Поиск по сайту скоро появится");
  };

  return (
    <form onSubmit={handleSubmit} className="relative mx-auto max-w-xl">
      <input
        type="text"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Найти"
        className="h-9 w-full rounded-lg border border-[var(--border)] bg-white pl-4 pr-10 text-sm outline-none focus:border-[var(--accent)]"
      />
      <button
        type="submit"
        className="absolute right-0 top-0 flex h-9 w-9 items-center justify-center text-[var(--text-muted)] hover:text-[var(--accent)]"
        aria-label="Искать"
      >
        <SearchIcon />
      </button>
    </form>
  );
}
