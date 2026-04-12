<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LandmarkMissingNavRule extends AbstractRule
{
    public function getId(): string { return 'landmark-missing-nav'; }
    public function getDescription(): string { return 'Navigation lists should be wrapped in a <nav> landmark for screen reader users.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Wrap navigation <ul>/<ol> lists in a <nav> element.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag   = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (in_array($tag, ['ul', 'ol'], true) && !isset($attrs['role'])) {
                    $children = $node['children'] ?? [];
                    $linkCount = 0;
                    foreach ($children as $child) {
                        if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === 'li') {
                            if ($this->hasChildTag($child, 'a')) {
                                $linkCount++;
                            }
                        }
                    }
                    if ($linkCount >= 3) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            'Navigation list with ' . $linkCount . ' links is not wrapped in a <nav> landmark.');
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function hasChildTag(array $node, string $tag): bool
    {
        foreach ($node['children'] ?? [] as $child) {
            if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === $tag) { return true; }
            if ($this->hasChildTag($child, $tag)) { return true; }
        }
        return false;
    }
}
