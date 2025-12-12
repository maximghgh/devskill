export function useDifficultyLabel() {
  function difficultyLabel(d) {
    if (d === "basic") return "Базовый";
    if (d === "middle") return "Средний";
    if (d === "advanced") return "Продвинутый";
    if (d === "mixed") return "Смешанный";
    return "—";
  }
  return { difficultyLabel };
}
