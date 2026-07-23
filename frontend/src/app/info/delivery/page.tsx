export const metadata = {
  title: "Доставка — GADGET·BAR",
};

export default function DeliveryPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Доставка</h1>
      <div className="max-w-3xl space-y-6 text-[var(--text-muted)]">
        <p>
          Мы доставляем заказы по городу и в регионы России и СНГ удобными для
          вас способами.
        </p>
        <h2 className="text-xl font-semibold text-[#1a1a1a]">По городу</h2>
        <p>
          Курьерская доставка по городу. Стоимость от 500 ₽ в зависимости от
          зоны. Бесплатная доставка возможна при заказе от определённой суммы —
          уточняйте у менеджера.
        </p>
        <h2 className="text-xl font-semibold text-[#1a1a1a]">По России и СНГ</h2>
        <p>
          Отправляем СДЭК, OZON, Boxberry и другими транспортными компаниями. В
          регионы и Республику Беларусь — только с 100% предоплатой.
        </p>
        <p>
          Сроки и точную стоимость уточняйте у менеджера после оформления
          заказа.
        </p>
      </div>
    </div>
  );
}
