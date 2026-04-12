<?php

namespace MalikAd778\BladeAlly\Rules\Headings;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HeadingUsedForStylingRule extends AbstractRule
{
    public function getId(): string { return 'heading-used-for-styling'; }
    public function getDescription(): string { return 'Heading tags should describe document structure, not be used purely for visual sizing.'; }
    public function getCategory(): string { return 'Headings'; }
    public function getDefaultSeverity(): string { return 'info'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Use CSS classes for visual styling instead of heading tags. Reserve headings for structural content.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag = strtolower($node['tagName'] ?? '');
                if (in_array($tag, ['h1','h2','h3','h4','h5','h6'], true)) {
                    $attrs   = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                    $classes = $attrs['class'] ?? '';
                    if (preg_match('/\b(text-(xs|sm|base|lg|xl|2xl|3xl|4xl)|font-(bold|semibold))\b/', $classes)) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "<{$tag}> appears to be used for visual styling (detected utility size classes). Use CSS classes instead.", 'info');
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
