"use client";

import { useState } from "react";
import Link from "next/link";
import { useAuthStore } from "@/stores/authStore";
import { toast } from "sonner";
import { formatPhone, phonePattern } from "@/lib/phone";

function YandexIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
      <circle cx="12" cy="12" r="12" fill="#fc3f1d" />
      <text
        x="12"
        y="16"
        textAnchor="middle"
        fill="white"
        fontSize="12"
        fontWeight="bold"
        fontFamily="Arial, sans-serif"
      >
        Я
      </text>
    </svg>
  );
}

function VkIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5 text-[#0077ff]">
      <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.12-5.339-3.202C4.624 10.857 4 8.673 4 8.2c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.779.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.745-.576.745z" />
    </svg>
  );
}

export default function RegisterForm({
  onSuccess,
  showTitle = true,
  showSocial = true,
}: {
  onSuccess?: () => void;
  showTitle?: boolean;
  showSocial?: boolean;
}) {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [privacy, setPrivacy] = useState(false);
  const [loading, setLoading] = useState(false);

  const authRegister = useAuthStore((s) => s.register);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!privacy) {
      toast.error("Необходимо согласиться с политикой конфиденциальности");
      return;
    }
    setLoading(true);
    try {
      await authRegister({
        name,
        email,
        phone: phone || undefined,
        password,
        password_confirmation: passwordConfirmation,
        privacy: true,
      });
      toast.success("Регистрация прошла успешно");
      onSuccess?.();
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Ошибка регистрации");
    } finally {
      setLoading(false);
    }
  };

  const handleSocial = (provider: string) => {
    toast.info(`Авторизация через ${provider} требует настройки приложения`);
  };

  const inputClass =
    "w-full rounded-lg border border-[var(--border)] px-4 py-2.5 outline-none transition focus:border-[var(--accent)]";

  return (
    <form onSubmit={submit} className="space-y-4">
      {showTitle && (
        <h1 className="mb-6 text-center text-3xl font-semibold">Регистрация</h1>
      )}

      <div>
        <label className="mb-1 block text-sm text-[var(--text-muted)]">
          Фамилия Имя Отчество <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
          className={inputClass}
        />
      </div>

      <div>
        <label className="mb-1 block text-sm text-[var(--text-muted)]">
          E-mail <span className="text-red-500">*</span>
        </label>
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          className={inputClass}
        />
        <p className="mt-1 text-xs text-[var(--text-muted)]">
          Является также логином для входа на сайт
        </p>
      </div>

      <div>
        <label className="mb-1 block text-sm text-[var(--text-muted)]">Телефон</label>
        <input
          type="tel"
          value={phone}
          onChange={(e) => setPhone(formatPhone(e.target.value))}
          placeholder="+7 (999) 999-99-99"
          pattern={phonePattern}
          className={inputClass}
        />
      </div>

      <div>
        <label className="mb-1 block text-sm text-[var(--text-muted)]">
          Пароль <span className="text-red-500">*</span>
        </label>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          minLength={6}
          className={inputClass}
        />
        <p className="mt-1 text-xs text-[var(--text-muted)]">
          Длина пароля не менее 6 символов
        </p>
      </div>

      <div>
        <label className="mb-1 block text-sm text-[var(--text-muted)]">
          Подтверждение пароля <span className="text-red-500">*</span>
        </label>
        <input
          type="password"
          value={passwordConfirmation}
          onChange={(e) => setPasswordConfirmation(e.target.value)}
          required
          minLength={6}
          className={inputClass}
        />
      </div>

      <label className="flex items-start gap-3 text-sm text-[var(--text-muted)]">
        <input
          type="checkbox"
          checked={privacy}
          onChange={(e) => setPrivacy(e.target.checked)}
          required
          className="mt-0.5 h-4 w-4 rounded border-[var(--border)]"
        />
        <span>
          Продолжая, вы соглашаетесь с{" "}
          <Link
            href="/privacy"
            className="text-[var(--accent)] hover:underline"
            target="_blank"
          >
            политикой конфиденциальности
          </Link>
        </span>
      </label>

      <button
        type="submit"
        disabled={loading}
        className="w-full rounded-lg bg-[#1a1a1a] py-3 font-medium text-white transition hover:bg-black disabled:opacity-60"
      >
        {loading ? "Регистрация..." : "Зарегистрироваться"}
      </button>

      {showSocial && (
        <div className="pt-4">
          <p className="mb-4 text-center text-sm text-[var(--text-muted)]">
            Войти с помощью
          </p>
          <div className="grid grid-cols-2 gap-3">
            <button
              type="button"
              onClick={() => handleSocial("Яндекс")}
              className="flex items-center justify-center gap-2 rounded-lg bg-[var(--muted)] py-2.5 text-sm font-medium transition hover:bg-gray-200"
            >
              <YandexIcon />
              Яндекс
            </button>
            <button
              type="button"
              onClick={() => handleSocial("VK")}
              className="flex items-center justify-center gap-2 rounded-lg bg-[var(--muted)] py-2.5 text-sm font-medium transition hover:bg-gray-200"
            >
              <VkIcon />
              VKontakte
            </button>
          </div>
        </div>
      )}
    </form>
  );
}
