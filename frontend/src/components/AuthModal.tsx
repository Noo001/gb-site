"use client";

import { useState } from "react";
import { useAuthStore } from "@/stores/authStore";
import { toast } from "sonner";
import RegisterForm from "./RegisterForm";

export default function AuthModal({
  isOpen,
  onClose,
}: {
  isOpen: boolean;
  onClose: () => void;
}) {
  const [mode, setMode] = useState<"login" | "register">("login");
  const [isForgot, setIsForgot] = useState(false);
  const [login, setLogin] = useState("");
  const [password, setPassword] = useState("");
  const [remember, setRemember] = useState(false);
  const [loading, setLoading] = useState(false);

  const authLogin = useAuthStore((s) => s.login);

  if (!isOpen) return null;

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await authLogin({ login, password, remember });
      toast.success("Вы вошли в аккаунт");
      onClose();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Ошибка");
    } finally {
      setLoading(false);
    }
  };

  const handleSocial = (provider: string) => {
    toast.info(`Авторизация через ${provider} требует настройки приложения`);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <button
          onClick={onClose}
          className="absolute right-4 top-4 text-2xl text-[var(--text-muted)] hover:text-[#1a1a1a]"
        >
          ×
        </button>

        <h2 className="mb-6 text-xl font-semibold">
          {isForgot
            ? "Восстановление пароля"
            : mode === "login"
            ? "Вход в личный кабинет"
            : "Регистрация"}
        </h2>

        {isForgot ? (
          <form
            onSubmit={(e) => {
              e.preventDefault();
              toast.info("Восстановление пароля в разработке");
              setIsForgot(false);
            }}
            className="space-y-4"
          >
            <div>
              <label className="mb-1 block text-sm text-[var(--text-muted)]">
                Email или телефон
              </label>
              <input
                type="text"
                required
                className="w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none focus:border-[var(--accent)]"
              />
            </div>
            <button className="w-full rounded-lg bg-[#1a1a1a] py-3 font-medium text-white transition hover:bg-black">
              Продолжить
            </button>
            <button
              type="button"
              onClick={() => setIsForgot(false)}
              className="w-full text-center text-sm text-[var(--accent)]"
            >
              ← Назад ко входу
            </button>
          </form>
        ) : mode === "register" ? (
            <RegisterForm onSuccess={onClose} showTitle={false} showSocial={false} />
          ) : (
            <form onSubmit={handleLogin} className="space-y-4">
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
                  minLength={6}
                  className="w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none focus:border-[var(--accent)]"
                />
              </div>

              <div className="flex items-center justify-between text-sm">
                <label className="flex items-center gap-2 text-[var(--text-muted)]">
                  <input
                    type="checkbox"
                    checked={remember}
                    onChange={(e) => setRemember(e.target.checked)}
                    className="h-4 w-4 rounded border-[var(--border)]"
                  />
                  Запомнить меня
                </label>
                <button
                  type="button"
                  onClick={() => setIsForgot(true)}
                  className="text-[var(--accent)] hover:underline"
                >
                  Забыли пароль?
                </button>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="w-full rounded-lg bg-[#1a1a1a] py-3 font-medium text-white transition hover:bg-black disabled:opacity-60"
              >
                {loading ? "Загрузка..." : "Продолжить"}
              </button>
            </form>
        )}

        {!isForgot && (
          <>
            <button
              onClick={() => setMode(mode === "login" ? "register" : "login")}
              className="mt-4 w-full rounded-lg bg-[var(--muted)] py-3 text-sm font-medium transition hover:bg-gray-200"
            >
              {mode === "login" ? "Регистрация" : "Уже есть аккаунт"}
            </button>

            <div className="mt-6">
              <div className="mb-4 text-center text-sm text-[var(--text-muted)]">
                Войти с помощью
              </div>
              <div className="grid grid-cols-2 gap-3">
                <button
                  onClick={() => handleSocial("Яндекс")}
                  className="flex items-center justify-center gap-2 rounded-lg bg-[#f5f5f5] py-2.5 text-sm font-medium transition hover:bg-gray-200"
                >
                  <span className="text-[#fc3f1d]">Я</span> Яндекс
                </button>
                <button
                  onClick={() => handleSocial("VK")}
                  className="flex items-center justify-center gap-2 rounded-lg bg-[#f5f5f5] py-2.5 text-sm font-medium transition hover:bg-gray-200"
                >
                  <span className="text-[#0077ff]">VK</span> VKontakte
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
