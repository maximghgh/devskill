const FILE_ICON_BASE = "/img/file-icons";
const DEFAULT_FILE_ICON = "/img/document.svg";

function extractFileReference(value) {
  if (!value) return "";

  if (typeof value === "string") {
    return value;
  }

  if (typeof value === "object") {
    if (typeof value.name === "string" && value.name) {
      return value.name;
    }

    if (typeof value.path === "string" && value.path) {
      return value.path;
    }
  }

  return "";
}

export function normalizeChapterFilePath(raw) {
  if (!raw || typeof raw !== "string") return "";
  if (/^https?:\/\//i.test(raw)) return raw;
  if (raw.startsWith("data:") || raw.startsWith("blob:")) return raw;
  if (raw.startsWith("/")) return raw;
  if (raw.startsWith("storage/")) return `/${raw}`;
  if (raw.startsWith("public/")) return `/${raw.replace(/^public\//, "storage/")}`;
  if (raw.startsWith("chapters_files/")) return `/storage/${raw}`;
  return `/storage/${raw.replace(/^\/+/, "")}`;
}

export function getChapterFileExt(value) {
  const reference = extractFileReference(value);

  if (!reference) return "";

  const clean = reference.split("?")[0].split("#")[0];
  const parts = clean.split(".");

  return parts.length > 1 ? parts.pop().toLowerCase() : "";
}

export function getChapterFileName(value) {
  const reference = extractFileReference(value);

  if (!reference) return "Файл";

  const clean = reference.split("?")[0].split("#")[0];
  return clean.split("/").pop() || "Файл";
}

export function getChapterFileIcon(value) {
  const ext = getChapterFileExt(value);

  if (!ext) return DEFAULT_FILE_ICON;

  if (["jpg", "jpeg", "png", "gif", "webp", "bmp"].includes(ext)) {
    return `${FILE_ICON_BASE}/JPG.svg`;
  }

  if (ext === "pdf") {
    return `${FILE_ICON_BASE}/PDF.svg`;
  }

  if (["ppt", "pptx", "pps", "ppsx", "pptm"].includes(ext)) {
    return `${FILE_ICON_BASE}/PPTX.png`;
  }

  if (["doc", "docx"].includes(ext)) {
    return `${FILE_ICON_BASE}/docx.png`;
  }

  if (["rar", "zip", "7z"].includes(ext)) {
    return `${FILE_ICON_BASE}/RAR.svg`;
  }

  return DEFAULT_FILE_ICON;
}

export function getChapterFileLabel(value) {
  const ext = getChapterFileExt(value);

  if (!ext) return "Файл";
  if (["doc", "docx"].includes(ext)) return "Документ Word";
  if (ext === "pdf") return "PDF файл";
  if (["jpg", "jpeg", "png", "gif", "webp", "bmp"].includes(ext)) return "Изображение";
  if (["ppt", "pptx", "pps", "ppsx", "pptm"].includes(ext)) return "Презентация";
  if (["rar", "zip", "7z"].includes(ext)) return "Архив";

  return `Файл .${ext}`;
}
