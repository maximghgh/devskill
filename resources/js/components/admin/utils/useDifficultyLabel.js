export function useDifficultyLabel() {
  function difficultyLabel(d) {
    if (d === "basic") return "Базовый";
    if (d === "middle") return "Фундаментальный";
    if (d === "advanced") return "Олимпиадный";
    if (d === "mixed") return "Смешанный";
    return "—";
  }
  return { difficultyLabel };
}
