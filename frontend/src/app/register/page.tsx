"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import RegisterForm from "@/components/RegisterForm";

export default function RegisterPage() {
  const router = useRouter();

  return (
    <div className="container-theme pb-12 pt-8">
      <nav className="mb-6 text-sm text-[var(--text-muted)]">
        <Link href="/" className="hover:text-[var(--accent)]">
          Главная
        </Link>
        <span className="mx-2">—</span>
        <Link href="/login" className="hover:text-[var(--accent)]">
          Авторизация
        </Link>
        <span className="mx-2">—</span>
        <span className="text-[#1a1a1a]">Регистрация</span>
      </nav>

      <div className="mx-auto max-w-lg rounded-2xl border border-[var(--border)] bg-white p-8">
        <RegisterForm onSuccess={() => router.push("/")} showTitle={false} />
      </div>
    </div>
  );
}
