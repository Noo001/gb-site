"use client";

import { sanitizeHtml } from "@/lib/sanitize";
import { useState } from "react";

export type ProductAttribute = {
  name: string;
  value: string;
  unit?: string | null;
};

type Tab = "Описание" | "Характеристики" | "Оплата" | "Доставка";

const tabs: Tab[] = ["Описание", "Характеристики", "Оплата", "Доставка"];

const paymentContent = `
<ul class="list-disc pl-5 space-y-2">
  <li><strong>Наличный расчёт</strong> — оплата при получении в магазине или курьеру.</li>
  <li><strong>Безналичный расчёт</strong> — банковской картой онлайн или через терминал.</li>
  <li><strong>Безналичный расчёт для юридических лиц</strong> — по счёту с НДС.</li>
  <li><strong>Рассрочка и кредит</strong> — оформление через партнёров банков.</li>
  <li><strong>Trade-In</strong> — обмен старого устройства на новое с доплатой.</li>
</ul>
`;

const deliveryContent = `
<ul class="list-disc pl-5 space-y-2">
  <li><strong>По городу</strong> — доставка курьером, стоимость от 500 ₽ в зависимости от зоны.</li>
  <li><strong>Самовывоз</strong> — бесплатно из пункта выдачи или магазина.</li>
  <li><strong>По России и СНГ</strong> — СДЭК / OZON / Boxberry. В регионы и Республику Беларусь отправляется с 100% предоплатой.</li>
  <li>Сроки и точную стоимость уточняйте у менеджера после оформления заказа.</li>
</ul>
`;

type ProductTabsProps = {
  productName: string;
  description: string | null;
  attributes?: ProductAttribute[];
};

export default function ProductTabs({
  productName,
  description,
  attributes,
}: ProductTabsProps) {
  const [activeTab, setActiveTab] = useState<Tab>("Описание");

  const renderContent = () => {
    switch (activeTab) {
      case "Описание":
        return description ? (
          <div
            className="prose max-w-none text-[var(--text-muted)]"
            dangerouslySetInnerHTML={{ __html: sanitizeHtml(description) }}
          />
        ) : (
          <p className="text-[var(--text-muted)]">
            Описание товара скоро появится.
          </p>
        );

      case "Характеристики":
        return attributes && attributes.length > 0 ? (
          <div className="overflow-hidden rounded-xl border border-[var(--border)] bg-white">
            <table className="w-full text-sm">
              <tbody>
                {attributes.map((attr, idx) => (
                  <tr
                    key={`${attr.name}-${idx}`}
                    className="border-b border-[var(--border)] last:border-0"
                  >
                    <td className="w-1/3 bg-[var(--muted)] px-4 py-3 font-medium text-[var(--text-muted)]">
                      {attr.name}
                    </td>
                    <td className="px-4 py-3">
                      {attr.value}
                      {attr.unit ? ` ${attr.unit}` : ""}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="text-[var(--text-muted)]">
            Характеристики товара скоро появятся.
          </p>
        );

      case "Оплата":
        return (
          <div
            className="prose max-w-none text-[var(--text-muted)]"
            dangerouslySetInnerHTML={{ __html: sanitizeHtml(paymentContent) }}
          />
        );

      case "Доставка":
        return (
          <div
            className="prose max-w-none text-[var(--text-muted)]"
            dangerouslySetInnerHTML={{ __html: sanitizeHtml(deliveryContent) }}
          />
        );
    }
  };

  return (
    <div className="mt-12">
      <div className="flex gap-6 border-b border-[var(--border)]">
        {tabs.map((tab) => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`pb-3 text-sm font-medium transition ${
              tab === activeTab
                ? "border-b-2 border-[var(--accent)] text-[#1a1a1a]"
                : "text-[var(--text-muted)] hover:text-[#1a1a1a]"
            }`}
          >
            {tab}
          </button>
        ))}
      </div>

      <div className="py-6">
        <h2 className="mb-4 text-2xl font-semibold">
          Купить {productName}
        </h2>
        {renderContent()}
      </div>
    </div>
  );
}
