/**
 * Работа с размеченным текстом (ответы на частые вопросы).
 *
 * Разметку чистит и сервер, но перед выводом через v-html проверяем ещё раз:
 * в базе могут лежать записи, добавленные в обход формы.
 */

const ALLOWED_TAGS = new Set([
    "P",
    "BR",
    "B",
    "STRONG",
    "I",
    "EM",
    "U",
    "S",
    "UL",
    "OL",
    "LI",
    "A",
    "H3",
    "H4",
    "DIV",
]);

/** Эти вырезаем вместе с содержимым. */
const DROPPED_TAGS = new Set([
    "SCRIPT",
    "STYLE",
    "IFRAME",
    "OBJECT",
    "EMBED",
    "TEMPLATE",
]);

const SAFE_PROTOCOLS = ["http:", "https:", "mailto:", "tel:"];

function escapeText(value) {
    const holder = document.createElement("div");
    holder.textContent = String(value ?? "");
    return holder.innerHTML;
}

function isSafeHref(value) {
    try {
        return SAFE_PROTOCOLS.includes(
            new URL(value, window.location.origin).protocol
        );
    } catch {
        return false;
    }
}

/** Оставляем только разрешённые теги, остальные разворачиваем в содержимое. */
export function sanitizeHtml(html) {
    const template = document.createElement("template");
    template.innerHTML = String(html ?? "");

    const walk = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) return;

            if (child.nodeType !== Node.ELEMENT_NODE) {
                child.remove();
                return;
            }

            if (DROPPED_TAGS.has(child.tagName)) {
                child.remove();
                return;
            }

            if (!ALLOWED_TAGS.has(child.tagName)) {
                // Тег не разрешён — оставляем то, что внутри
                walk(child);
                child.replaceWith(...child.childNodes);
                return;
            }

            [...child.attributes].forEach((attr) => {
                const keepHref =
                    child.tagName === "A" &&
                    attr.name === "href" &&
                    isSafeHref(attr.value);

                if (!keepHref) child.removeAttribute(attr.name);
            });

            if (child.tagName === "A") {
                child.setAttribute("target", "_blank");
                child.setAttribute("rel", "noopener noreferrer");
            }

            walk(child);
        });
    };

    walk(template.content);
    return template.innerHTML;
}

/** Есть ли в разметке хоть какой-то текст (пустой contenteditable даёт <br>). */
export function htmlHasText(html) {
    const template = document.createElement("template");
    template.innerHTML = String(html ?? "");

    return (template.content.textContent ?? "").trim() !== "";
}

/** Текст со переносами -> абзацы, чтобы старый ответ открылся в редакторе. */
export function textToHtml(text) {
    const value = String(text ?? "").trim();
    if (!value) return "";

    return value
        .split(/\n+/)
        .filter(Boolean)
        .map((line) => `<p>${escapeText(line)}</p>`)
        .join("");
}

/** Разметка ответа для редактирования: готовый HTML либо старый плоский текст. */
export function answerToHtml(faq) {
    const html = String(faq?.answer_html ?? "").trim();
    if (html) return sanitizeHtml(html);

    return textToHtml(faq?.answer);
}

/** Готовый HTML ответа для показа на сайте. */
export function renderFaqAnswer(faq) {
    const html = String(faq?.answer_html ?? "").trim();
    if (html) return sanitizeHtml(html);

    return textToHtml(faq?.answer);
}
