<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ListNotSemanticRule extends AbstractRule
{
    public function getId(): string { return 'list-not-semantic'; }
    public function getDescription(): string { return 'Visual list patterns using <div> or <span> should use semantic <ul>/<ol> markup.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'info'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Replace div/span-based lists with <ul> or <ol> elements.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag      = strtolower($node['tagName'] ?? '');
                $attrs    = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $classes  = $attrs['class'] ?? '';
                $role     = $attrs['role'] ?? '';
                if (in_array($tag, ['div', 'span'], true) && !$role) {
                    $children = $node['children'] ?? [];
                    $sameTagCount = 0;
                    foreach ($children as $child) {
                        if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === $tag) {
                            $sameTagCount++;
                        }
                    }
                    if ($sameTagCount >= 3 && preg_match('/\b(list|item|row|entry)\b/i', $classes)) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "Possible list pattern using <{$tag}> without semantic list markup.", 'info');
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
