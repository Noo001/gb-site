export const metadata = {
  title: "Гарантия — GADGET·BAR",
};

export default function WarrantyPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">Гарантия</h1>
      <div className="max-w-3xl space-y-6 text-[var(--text-muted)]">
        <p>
          На все товары в GADGET·BAR действует официальная гарантия
          производителя. Срок гарантии указан на странице товара и в
          сопроводительных документах.
        </p>
        <ul className="list-disc space-y-2 pl-5">
          <li>Гарантийное обслуживание в авторизованных сервисных центрах.</li>
          <li>Помощь в оформлении гарантийного случая.</li>
          <li>
            Консультации по использованию устройства после покупки.
          </li>
        </ul>
      </div>
    </div>
  );
}
