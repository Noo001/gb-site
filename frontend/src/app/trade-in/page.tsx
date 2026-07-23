export const metadata = {
  title: "Программа Trade-In — GADGET·BAR",
};

export default function TradeInPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Программа Trade-In</h1>
      <div className="max-w-3xl space-y-4 text-[var(--text-muted)]">
        <p>
          Обменяйте старое устройство на новое с доплатой. Мы принимаем
          смартфоны, планшеты, наушники и другую технику.
        </p>
        <ul className="list-disc space-y-2 pl-5">
          <li>Оценка устройства за несколько минут.</li>
          <li>Скидка на покупку нового гаджета.</li>
          <li>Безопасное удаление данных.</li>
        </ul>
      </div>
    </div>
  );
}
