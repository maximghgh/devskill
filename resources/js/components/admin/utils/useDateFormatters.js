export function useDateFormatters() {
  const fmtShort = new Intl.DateTimeFormat("ru-RU", {
    day: "2-digit", month: "2-digit", year: "numeric",
  });

  const fmtLong = new Intl.DateTimeFormat("ru-RU", {
    day: "2-digit", month: "long", year: "numeric",
  });

  function toDateFromISO(iso) {
    if (!iso) return null;
    if (typeof iso === "string" && /^\d{4}-\d{2}-\d{2}$/.test(iso)) {
      const [y, m, d] = iso.split("-").map(Number);
      return new Date(y, m - 1, d);
    }
    const d = new Date(iso);
    return isNaN(+d) ? null : d;
  }

  function formatBirthday(value, variant = "short") {
    const date = toDateFromISO(value);
    if (!date) return "—";
    return variant === "long" ? fmtLong.format(date) : fmtShort.format(date);
  }

  function formatDateTime(iso) {
    if (!iso) return "—";
    const d = new Date(iso);
    if (isNaN(+d)) return "—";
    return d.toLocaleString("ru-RU", {
      day: "2-digit", month: "2-digit", year: "numeric",
      hour: "2-digit", minute: "2-digit",
    });
  }

  return { formatBirthday, formatDateTime };
}
