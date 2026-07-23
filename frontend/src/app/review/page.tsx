export const metadata = {
  title: "Оставить отзыв — GADGET·BAR",
};

export default function ReviewPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Оставить отзыв</h1>
      <div className="max-w-xl rounded-xl border border-[var(--border)] bg-white p-6">
        <p className="mb-4 text-[var(--text-muted)]">
          Расскажите о вашем опыте покупки. Ваш отзыв поможет нам стать лучше.
        </p>
        <textarea
          rows={5}
          className="mb-4 w-full rounded-lg border border-[var(--border)] px-4 py-3 outline-none focus:border-[var(--accent)]"
          placeholder="Ваш отзыв"
        />
        <button className="rounded-lg bg-[#1a1a1a] px-6 py-2.5 font-medium text-white transition hover:bg-black">
          Отправить
        </button>
      </div>
    </div>
  );
}
