import cardPlaceholder from "../../img/logo.png";
import descriptionPlaceholder from "../../img/img__course.png";

function normalizeImageUrl(value) {
    if (typeof value !== "string") {
        return "";
    }

    const normalized = value.trim();

    if (!normalized) {
        return "";
    }

    if (
        normalized.startsWith("http://") ||
        normalized.startsWith("https://") ||
        normalized.startsWith("//") ||
        normalized.startsWith("data:") ||
        normalized.startsWith("blob:")
    ) {
        return normalized;
    }

    return `/${normalized.replace(/^\/+/, "")}`;
}

export function getCourseCardImageUrl(course) {
    return normalizeImageUrl(course?.card_image) || cardPlaceholder;
}

export function getCourseDescriptionImageUrl(course) {
    return (
        normalizeImageUrl(course?.description_image) || descriptionPlaceholder
    );
}
