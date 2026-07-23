function DeliveryIcon({ className }: { className?: string }) {
  return (
    <svg className={className} width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="6" width="15" height="12" rx="2" />
      <circle cx="7.5" cy="17.5" r="1.5" />
      <circle cx="17.5" cy="17.5" r="1.5" />
      <path d="M18 12h2a1 1 0 0 1 1 1v3" />
    </svg>
  );
}

function SupportIcon({ className }: { className?: string }) {
  return (
    <svg className={className} width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
      <path d="M17 18a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2Z" />
      <path d="M15 11v2" />
      <path d="M12 11v2" />
      <path d="M9 11v2" />
    </svg>
  );
}

function GiftIcon({ className }: { className?: string }) {
  return (
    <svg className={className} width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="8" width="18" height="4" rx="1" />
      <path d="M12 8v13" />
      <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7" />
      <path d="M7.5 8a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 2.5 2.5v2.5" />
      <path d="M16.5 8V6a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1-2.5 2.5h-2.5" />
    </svg>
  );
}

function CalendarIcon({ className }: { className?: string }) {
  return (
    <svg className={className} width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="4" width="18" height="18" rx="2" />
      <path d="M16 2v4" />
      <path d="M8 2v4" />
      <path d="M3 10h18" />
      <path d="M8 14h.01" />
      <path d="M12 14h.01" />
      <path d="M16 14h.01" />
      <path d="M8 18h.01" />
      <path d="M12 18h.01" />
    </svg>
  );
}

const features = [
  {
    icon: DeliveryIcon,
    title: "Быстрая доставка",
    text: "Если выглянуть в окно, можно услышать, как торопится наш курьер",
  },
  {
    icon: SupportIcon,
    title: "Клиентский сервис",
    text: "Если что-то пошло не так, мы вам поможем",
  },
  {
    icon: GiftIcon,
    title: "Акции и скидки",
    text: "Специально для вас, каждую неделю",
  },
  {
    icon: CalendarIcon,
    title: "15 лет с вами",
    text: "С нами Вы будете довольны в своих гаджетах!",
  },
];

export default function Features() {
  return (
    <section className="container-theme mb-12">
      <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        {features.map((f) => (
          <div key={f.title} className="flex gap-4">
            <f.icon className="shrink-0 text-[#1a1a1a]" />
            <div>
              <h3 className="mb-1 font-semibold">{f.title}</h3>
              <p className="text-sm leading-relaxed text-[var(--text-muted)]">{f.text}</p>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}
