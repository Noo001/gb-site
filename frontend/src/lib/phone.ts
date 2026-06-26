export function formatPhone(value: string): string {
  const digits = value.replace(/\D/g, "");
  if (!digits) return "";

  let rest = digits;
  if (rest.startsWith("7") || rest.startsWith("8")) {
    rest = rest.slice(1);
  }
  if (rest.length > 10) {
    rest = rest.slice(0, 10);
  }

  let out = "+7";
  if (rest.length > 0) out += ` (${rest.slice(0, 3)}`;
  if (rest.length >= 4) out += `) ${rest.slice(3, 6)}`;
  if (rest.length >= 7) out += `-${rest.slice(6, 8)}`;
  if (rest.length >= 9) out += `-${rest.slice(8, 10)}`;

  return out;
}

export const phonePattern =
  "^(\\+7|7|8)?[\\s\\-]?\\(?[0-9]{3}\\)?[\\s\\-]?[0-9]{3}[\\s\\-]?[0-9]{2}[\\s\\-]?[0-9]{2}$";
