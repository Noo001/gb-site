"use client";

import { useEffect, useState } from "react";
import Link from "next/link";

const CONSENT_KEY = "gb-cookie-consent";

export default function CookieConsent() {
  const [mounted, setMounted] = useState(false);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    setMounted(true);
    if (typeof window !== "undefined") {
      setVisible(localStorage.getItem(CONSENT_KEY) !== "1");
    }
  }, []);

  const handleAccept = () => {
    localStorage.setItem(CONSENT_KEY, "1");
    setVisible(false);
  };

  if (!mounted || !visible) {
    return null;
  }

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 p-4">
      <div className="container-theme">
        <div className="rounded-xl border border-[var(--border)] bg-white p-4 shadow-lg sm:p-5">
          <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
            <p className="text-center text-sm text-[var(--text-muted)] sm:text-left">
              Мы используем файлы cookie, чтобы улучшить работу сайта.
              Продолжая пользоваться сайтом, вы соглашаетесь с{" "}
              <Link
                href="/privacy"
                className="text-[var(--accent)] hover:text-[var(--accent-hover)]"
              >
                политикой конфиденциальности
              </Link>
              .
            </p>
            <button
              onClick={handleAccept}
              className="shrink-0 rounded-full bg-[#1a1a1a] px-6 py-2.5 text-sm font-medium text-white transition hover:bg-black"
            >
              Хорошо
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
