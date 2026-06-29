import Link from "next/link";

export default function NotFound() {
  return (
    <div className="container-theme flex min-h-[60vh] flex-col items-center justify-center pb-12 pt-12 text-center">
      <h1 className="mb-4 text-8xl font-bold text-[#1a1a1a]">404</h1>
      <p className="mb-8 max-w-md text-lg text-[var(--text-muted)]">
        Страница не найдена. Возможно, она была удалена или адрес введён с ошибкой.
      </p>
      <Link
        href="/"
        className="rounded-lg bg-[#1a1a1a] px-6 py-3 font-medium text-white transition hover:bg-black"
      >
        Вернуться на главную
      </Link>
    </div>
  );
}
