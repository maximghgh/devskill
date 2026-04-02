const actualOptions = [
  {
    value: "beginner_year_1",
    label: "Начинающий (1 год обучения)",
    cardClass: "course__card_bg-cyan",
    blockClass: "block-info_bg-cyan",
    badgeClass: "users-role-pill--beginner-year-1",
  },
  {
    value: "beginner_year_2",
    label: "Начинающий (2 год обучения)",
    cardClass: "course__card_bg-green",
    blockClass: "block-info_bg-green",
    badgeClass: "users-role-pill--beginner-year-2",
  },
  {
    value: "basic",
    label: "Базовый",
    cardClass: "course__card_bg-cyan",
    blockClass: "block-info_bg-cyan",
    badgeClass: "users-role-pill--basic",
  },
  {
    value: "fundamental",
    label: "Фундаментальный",
    cardClass: "course__card_bg-fiolet",
    blockClass: "block-info_bg-fiolet",
    badgeClass: "users-role-pill--fundamental",
  },
  {
    value: "olympiad",
    label: "Олимпиадный",
    cardClass: "course__card_bg-orange",
    blockClass: "block-info_bg-orange",
    badgeClass: "users-role-pill--olympiad",
  },
];

const legacyOptions = [
  {
    value: "middle",
    label: "Фундаментальный",
    cardClass: "course__card_bg-fiolet",
    blockClass: "block-info_bg-fiolet",
    badgeClass: "users-role-pill--fundamental",
    legacy: true,
  },
  {
    value: "advanced",
    label: "Олимпиадный",
    cardClass: "course__card_bg-orange",
    blockClass: "block-info_bg-orange",
    badgeClass: "users-role-pill--olympiad",
    legacy: true,
  },
  {
    value: "mixed",
    label: "Смешанный",
    cardClass: "course__card_bg-green",
    blockClass: "block-info_bg-green",
    badgeClass: "users-role-pill--legacy",
    legacy: true,
  },
];

const allOptions = [...actualOptions, ...legacyOptions];

function normalizeValue(value) {
  return String(value || "").trim().toLowerCase();
}

export const COURSE_DIFFICULTY_OPTIONS = actualOptions;

export function getCourseDifficultyMeta(value) {
  const normalized = normalizeValue(value);
  return (
    allOptions.find((option) => option.value === normalized) || {
      value: normalized,
      label: "—",
      cardClass: "",
      blockClass: "",
      badgeClass: "users-role-pill--default",
    }
  );
}

export function getCourseDifficultyLabel(value) {
  return getCourseDifficultyMeta(value).label;
}

export function getCourseDifficultyCardClass(value) {
  return getCourseDifficultyMeta(value).cardClass;
}

export function getCourseDifficultyBlockClass(value) {
  return getCourseDifficultyMeta(value).blockClass;
}

export function getCourseDifficultyBadgeClass(value) {
  return getCourseDifficultyMeta(value).badgeClass;
}

export function getCourseDifficultyOptions(currentValue = null) {
  const options = [...actualOptions];
  const currentMeta = currentValue ? getCourseDifficultyMeta(currentValue) : null;

  if (
    currentMeta &&
    currentMeta.value &&
    currentMeta.label !== "—" &&
    !options.some((option) => option.value === currentMeta.value)
  ) {
    options.push(currentMeta);
  }

  return options;
}

export function createCourseDifficultyDictionary(resolver) {
  return new Proxy(
    {},
    {
      get(_, property) {
        if (typeof property !== "string") return "";
        return resolver(property);
      },
    }
  );
}
