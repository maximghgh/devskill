<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /** Теги, разрешённые в ответе. Всё остальное разворачивается в текст. */
    private const ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's',
        'ul', 'ol', 'li', 'a', 'h3', 'h4',
    ];

    /** Блоки верхнего уровня: всё остальное на этом уровне заворачиваем в <p>. */
    private const BLOCK_TAGS = ['p', 'ul', 'ol', 'h3', 'h4'];

    /** Эти вырезаем вместе с содержимым. */
    private const DROPPED_TAGS = ['script', 'style', 'iframe', 'object', 'embed'];

    private const SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public function index()
    {
        $faqs = Faq::orderBy('id')->get();
        return response()->json($faqs);
    }

    public function store(Request $request)
    {
        $faq = Faq::create($this->payload($this->validated($request)));

        return response()->json($faq, 201);
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->payload($this->validated($request)));

        return response()->json($faq);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(null, 204);
    }

    /**
     * Ответ приходит размеченным HTML из редактора либо простым текстом
     * (старые записи и внешние вызовы).
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => 'required|string',
            'answer' => 'required_without:answer_html|nullable|string',
            'answer_html' => 'nullable|string',
        ]);
    }

    private function payload(array $data): array
    {
        $html = trim((string) ($data['answer_html'] ?? ''));

        if ($html === '') {
            // Пришёл только текст — сохраняем его и в HTML не превращаем.
            return [
                'question' => $data['question'],
                'answer' => (string) ($data['answer'] ?? ''),
                'answer_html' => null,
            ];
        }

        // Разметке из браузера не доверяем: чистим по белому списку здесь,
        // а не только на клиенте.
        $safeHtml = $this->sanitize($html);

        return [
            'question' => $data['question'],
            'answer' => $this->toPlainText($safeHtml),
            'answer_html' => $safeHtml !== '' ? $safeHtml : null,
        ];
    }

    /** Оставляем только разрешённые теги и безопасные ссылки. */
    private function sanitize(string $html): string
    {
        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="faq-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('faq-root');

        if (!$root) {
            return '';
        }

        $this->cleanChildren($root);
        $this->wrapLooseText($document, $root);
        $this->removeEmptyBlocks($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    /**
     * Строки, набранные в contenteditable, приходят голым текстом
     * или в <div>. Приводим верхний уровень к абзацам, чтобы у ответа
     * были нормальные отступы.
     */
    private function wrapLooseText(DOMDocument $document, DOMElement $root): void
    {
        $paragraph = null;

        foreach (iterator_to_array($root->childNodes) as $child) {
            $isBlock = $child instanceof DOMElement
                && in_array(strtolower($child->tagName), self::BLOCK_TAGS, true);

            if ($isBlock) {
                $paragraph = null;
                continue;
            }

            if ($child->nodeType === XML_TEXT_NODE && trim($child->textContent) === '') {
                $root->removeChild($child);
                continue;
            }

            if (!$paragraph) {
                $paragraph = $document->createElement('p');
                $root->insertBefore($paragraph, $child);
            }

            $paragraph->appendChild($child);
        }
    }

    /**
     * Браузер при форматировании оставляет пустые абзацы и списки —
     * выбрасываем их, чтобы в ответе не было дыр.
     */
    private function removeEmptyBlocks(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $this->removeEmptyBlocks($child);

            $tag = strtolower($child->tagName);

            if (!in_array($tag, ['p', 'h3', 'h4', 'li', 'ul', 'ol'], true)) {
                continue;
            }

            if (trim($child->textContent) === '') {
                $child->parentNode->removeChild($child);
            }
        }
    }

    private function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $this->cleanElement($child);
                continue;
            }

            // Комментарии и прочие узлы, кроме текста, не нужны.
            if ($child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private function cleanElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if (in_array($tag, self::DROPPED_TAGS, true)) {
            $element->parentNode->removeChild($element);
            return;
        }

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            $this->unwrap($element);
            return;
        }

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $keepHref = $tag === 'a'
                && strtolower($attribute->name) === 'href'
                && $this->isSafeHref($attribute->value);

            if (!$keepHref) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a') {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer');
        }

        $this->cleanChildren($element);
    }

    /** Убираем сам тег, оставляя его содержимое на месте. */
    private function unwrap(DOMElement $element): void
    {
        $this->cleanChildren($element);

        $parent = $element->parentNode;

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function isSafeHref(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        // Относительные ссылки допустимы, схему проверяем только если она есть.
        $scheme = parse_url($value, PHP_URL_SCHEME);

        return $scheme === null || in_array(strtolower($scheme), self::SAFE_SCHEMES, true);
    }

    /** HTML -> плоский текст: списки маркируем тире, блоки разделяем переносами. */
    private function toPlainText(string $html): string
    {
        $text = preg_replace('~<li[^>]*>~i', '— ', $html);
        $text = preg_replace('~</(p|li|h3|h4|ul|ol)>~i', "\n", (string) $text);
        $text = preg_replace('~<br\s*/?>~i', "\n", (string) $text);

        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = array_filter(
            array_map('trim', explode("\n", $text)),
            fn ($line) => $line !== ''
        );

        return implode("\n", $lines);
    }
}
