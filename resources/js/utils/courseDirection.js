const DIRECTION_CARD_CLASSES = [
  "course__card_bg-fiolet",
  "course__card_bg-cyan",
  "course__card_bg-green",
  "course__card_bg-orange",
  "course__card_bg-blue",
  "course__card_bg-plum",
  "course__card_bg-teal",
];

const FALLBACK_CARD_CLASS = "course__card_bg-fiolet";

export function getDirectionCardClass(direction) {
  const id = Number.parseInt(direction, 10);

  if (!Number.isFinite(id) || id <= 0) {
    return FALLBACK_CARD_CLASS;
  }

  return DIRECTION_CARD_CLASSES[(id - 1) % DIRECTION_CARD_CLASSES.length];
}

export function getDirectionBlockClass(direction) {
  return getDirectionCardClass(direction).replace("course__card_bg-", "block-info_bg-");
}
