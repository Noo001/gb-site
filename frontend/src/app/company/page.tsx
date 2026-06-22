export const metadata = {
  title: "О компании — GADGET·BAR",
};

export default function CompanyPage() {
  return (
    <div className="container-theme pb-12">
      <h1 className="mb-8 text-3xl font-semibold">О компании</h1>
      <div className="max-w-3xl space-y-4 text-[var(--text-muted)]">
        <p>
          GADGET·BAR — это сеть магазинов техники и аксессуаров. Мы предлагаем
          смартфоны, наушники, гаджеты для дома, игровые консоли и технику для
          кухни от ведущих мировых брендов.
        </p>
        <p>
          Наша цель — сделать покупку техники простой и выгодной: консультации
          экспертов, доставка, рассрочка и программа Trade-In.
        </p>
      </div>
    </div>
  );
}
