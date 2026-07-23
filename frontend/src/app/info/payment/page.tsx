export const metadata = {
  title: "Способы оплаты — GADGET·BAR",
};

export default function PaymentPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Способы оплаты</h1>
      <div className="max-w-3xl space-y-6 text-[var(--text-muted)]">
        <p>
          В GADGET·BAR доступны несколько способов оплаты. Выбирайте самый
          удобный:
        </p>
        <ul className="list-disc space-y-2 pl-5">
          <li>
            <strong>Наличный расчёт</strong> — оплатите заказ при получении в
            магазине или курьеру.
          </li>
          <li>
            <strong>Банковская карта</strong> — оплата онлайн или через
            терминал при получении.
          </li>
          <li>
            <strong>Безналичный расчёт для юридических лиц</strong> — оплата по
            счёту с НДС.
          </li>
          <li>
            <strong>Рассрочка и кредит</strong> — оформление через партнёрские
            банки.
          </li>
          <li>
            <strong>Trade-In</strong> — обменяйте старое устройство на новое с
            доплатой.
          </li>
        </ul>
      </div>
    </div>
  );
}
