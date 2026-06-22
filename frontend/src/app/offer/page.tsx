export const metadata = {
  title: "Публичная оферта — GADGET·BAR",
};

export default function OfferPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Публичная оферта</h1>
      <div className="max-w-3xl space-y-4 text-[var(--text-muted)]">
        <p>
          Настоящая оферта регулирует отношения между интернет-магазином
          GADGET·BAR и покупателем.
        </p>
        <p>
          Оформляя заказ, покупатель принимает условия доставки, оплаты и
          возврата, размещённые на сайте.
        </p>
      </div>
    </div>
  );
}
