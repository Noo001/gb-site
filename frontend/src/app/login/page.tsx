"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/stores/authStore";
import { toast } from "sonner";

export default function LoginPage() {
  const [login, setLogin] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const authLogin = useAuthStore((s) => s.login);
  const router = useRouter();

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await authLogin({ login, password });
      toast.success("Вы вошли в аккаунт");
      router.push("/");
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Ошибка");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container-theme flex justify-center pb-12 pt-8">
      <div className="w-full max-w-md rounded-2xl border border-[var(--border)] bg-white p-6">
        <h1 className="mb-6 text-2xl font-semibold">Вход в личный кабинет</h1>
        <form onSubmit={submit} className="space-y-4">
          <div>
            <label className="mb-1 block text-sm text-[var(--text-muted)]">
              Логин или номер телефона
            </label>
            <input
              type="text"
              value={login}
              onChange={(e) => setLogin(e.target.value)}
              required
              className="w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none focus:border-[var(--accent)]"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm text-[var(--text-muted)]">
              Пароль
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none focus:border-[var(--accent)]"
            />
          </div>
          <button
            disabled={loading}
            className="w-full rounded-lg bg-[#1a1a1a] py-3 font-medium text-white transition hover:bg-black disabled:opacity-60"
          >
            {loading ? "Вход..." : "Продолжить"}
          </button>
        </form>
      </div>
    </div>
  );
}
