export const metadata = {
  title: "Магазины — GADGET·BAR",
};

export default function StoresPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Магазины</h1>
      <div className="rounded-xl border border-[var(--border)] bg-white p-6">
        <h2 className="mb-4 text-xl font-semibold">Gadget-Bar в Воронеже</h2>
        <p className="text-[var(--text-muted)]">
          ул. Примерная, 123, ТЦ «Галерея»
        </p>
        <p className="mt-2 text-[var(--text-muted)]">
          Режим работы: ежедневно с 10:00 до 21:00
        </p>
      </div>
    </div>
  );
}
