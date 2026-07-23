import Link from "next/link";

export default function Breadcrumbs({
  items,
}: {
  items: { name: string; url: string }[];
}) {
  if (!items || items.length === 0) return null;

  return (
    <nav className="py-4 text-sm text-[var(--text-muted)]">
      {items.map((item, index) => (
        <span key={item.url + index}>
          {index > 0 && <span className="mx-2 text-[var(--border)]">/</span>}
          <Link href={item.url} className="hover:text-[var(--accent)]">
            {item.name}
          </Link>
        </span>
      ))}
    </nav>
  );
}
