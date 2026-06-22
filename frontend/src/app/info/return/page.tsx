export const metadata = {
  title: "Обмен и возврат — GADGET·BAR",
};

export default function ReturnPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Обмен и возврат</h1>
      <div className="max-w-3xl space-y-6 text-[var(--text-muted)]">
        <p>
          Вы можете вернуть или обменять товар в течение 14 дней с момента
          получения, если он не был в употреблении и сохранил товарный вид.
        </p>
        <ul className="list-disc space-y-2 pl-5">
          <li>
            Для возврата свяжитесь с менеджером по телефону или email.
          </li>
          <li>
            Возврат средств осуществляется тем же способом, которым была
            произведена оплата.
          </li>
          <li>
            На некоторые категории товаров (наушники, смартфоны и др.) могут
            действовать дополнительные условия.
          </li>
        </ul>
      </div>
    </div>
  );
}
