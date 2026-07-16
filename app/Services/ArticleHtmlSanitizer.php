<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleHtmlSanitizer
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        'p'          => [],
        'br'         => [],
        'hr'         => [],
        'h2'         => ['id'],
        'h3'         => ['id'],
        'h4'         => ['id'],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'strike'     => [],
        'code'       => ['class'],
        'pre'        => ['class'],
        'blockquote' => [],
        'ul'         => [],
        'ol'         => ['start'],
        'li'         => [],
        'a'          => ['href', 'title', 'rel', 'target'],
        'img'        => ['src', 'alt', 'title', 'width', 'height', 'loading', 'data-id'],
        'figure'     => [],
        'figcaption' => [],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tr'         => [],
        'th'         => ['colspan', 'rowspan'],
        'td'         => ['colspan', 'rowspan'],
        'span'       => ['class'],
        'div'        => ['class'],
    ];

    public function __construct(
        private readonly PublicHtmlStorageMirror $mirror,
    ) {}

    public function sanitize(?string $html): string
    {
        if (! is_string($html) || trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="__kindo_root">'.$html.'</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $dom->getElementById('__kindo_root');
        if (! $root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return '';
        }

        $this->scrubNode($root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $this->normalizeImageSources($output);
    }

    /**
     * Rewrite local storage URLs to stable /storage/... paths and mirror files.
     */
    public function normalizeImageSources(string $html): string
    {
        return (string) preg_replace_callback(
            '/(<img\b[^>]*\bsrc=["\'])([^"\']+)(["\'][^>]*>)/i',
            function (array $m): string {
                $src = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5);
                $path = $this->mirror->publicDiskPathFromUrl($src);

                // TipTap: broken/temporary src but durable data-id on the same tag
                if ($path === null && preg_match('/\bdata-id=["\']([^"\']+)["\']/i', $m[0], $idMatch)) {
                    $candidate = ltrim(str_replace('\\', '/', $idMatch[1]), '/');
                    if ($candidate !== '' && ! str_contains($candidate, '..') && $this->mirror->existsOnPublicDisk($candidate)) {
                        $path = $candidate;
                    }
                }

                if ($path === null) {
                    return $m[0];
                }

                $this->mirror->mirror($path);

                return $m[1].e(asset('storage/'.$path)).$m[3];
            },
            $html
        );
    }

    private function scrubNode(DOMNode $node): void
    {
        if (! $node->hasChildNodes()) {
            return;
        }

        /** @var list<DOMNode> $children */
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var DOMElement $child */
                $tag = strtolower($child->tagName);

                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button'], true)) {
                    $child->parentNode?->removeChild($child);

                    continue;
                }

                if (! array_key_exists($tag, self::ALLOWED)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $child->parentNode?->removeChild($child);

                    continue;
                }

                $this->scrubAttributes($child, self::ALLOWED[$tag]);
                $this->scrubNode($child);

                continue;
            }

            if ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    /**
     * @param  list<string>  $allowedAttrs
     */
    private function scrubAttributes(DOMElement $el, array $allowedAttrs): void
    {
        $tag = strtolower($el->tagName);
        /** @var list<string> $names */
        $names = [];

        if ($el->hasAttributes()) {
            foreach (iterator_to_array($el->attributes) as $attr) {
                $names[] = $attr->name;
            }
        }

        foreach ($names as $name) {
            $lower = strtolower($name);

            if (str_starts_with($lower, 'on')) {
                $el->removeAttribute($name);

                continue;
            }

            if (! in_array($lower, $allowedAttrs, true)) {
                $el->removeAttribute($name);

                continue;
            }

            $value = trim((string) $el->getAttribute($name));

            if (($lower === 'href' || $lower === 'src') && $this->isDangerousUrl($value)) {
                $el->removeAttribute($name);

                continue;
            }

            if ($lower === 'href' && $tag === 'a') {
                $el->setAttribute('rel', 'noopener noreferrer');
                if (str_starts_with(strtolower($value), 'http')) {
                    $el->setAttribute('target', '_blank');
                }
            }

            if ($tag === 'img' && $lower === 'src') {
                $el->setAttribute('loading', 'lazy');
                if (! $el->hasAttribute('alt')) {
                    $el->setAttribute('alt', '');
                }
            }

            if ($lower === 'class') {
                $parts = preg_split('/\s+/', $value) ?: [];
                $kept = array_values(array_filter($parts, function (string $class): bool {
                    return (bool) preg_match('/^(language-[\w+-]+|hljs[\w-]*|code-block[\w-]*)$/', $class);
                }));

                if ($kept === []) {
                    $el->removeAttribute('class');
                } else {
                    $el->setAttribute('class', implode(' ', $kept));
                }
            }
        }
    }

    private function isDangerousUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        if (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
            return false;
        }

        if (str_starts_with($url, 'data:image/')) {
            return false;
        }

        if (str_starts_with($url, 'data:')) {
            return true;
        }

        $lower = strtolower($url);

        return str_starts_with($lower, 'javascript:')
            || str_starts_with($lower, 'vbscript:')
            || str_starts_with($lower, 'file:');
    }
}
