import { getCourseDifficultyLabel } from "@/utils/courseDifficulty";

export function useDifficultyLabel() {
  function difficultyLabel(d) {
    return getCourseDifficultyLabel(d);
  }
  return { difficultyLabel };
}
