<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class SkipLinkMissingRule extends AbstractRule
{
    public function getId(): string { return 'skip-link-missing'; }
    public function getDescription(): string { return 'Layout templates should include a skip navigation link as the first focusable element.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '2.4.1 Bypass Blocks'; }
    public function getFixHint(): string { return 'Add <a href="#main" class="sr-only focus:not-sr-only">Skip to content</a> as the first element in layout.'; }

    public function check(array $ast, RuleContext $context): array
    {
        if (!$context->isLayoutTemplate()) {
            return [];
        }
        $firstLinks = $this->collectFirstLinks($ast);
        foreach ($firstLinks as $link) {
            $attrs = array_change_key_case($link['attributes'] ?? [], CASE_LOWER);
            $href  = $attrs['href'] ?? '';
            if (str_starts_with($href, '#')) {
                return [];
            }
        }
        return [$this->makeViolation($context->filePath, 1, 1,
            'Layout template is missing a skip navigation link (e.g. <a href="#main">Skip to content</a>).')];
    }

    private function collectFirstLinks(array $ast): array
    {
        $links = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') { $links[] = $node; }
            if (!empty($node['children'])) { $links = array_merge($links, $this->collectFirstLinks($node['children'])); }
            if (count($links) >= 5) { break; }
        }
        return $links;
    }
}
