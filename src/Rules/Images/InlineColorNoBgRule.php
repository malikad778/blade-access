<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class InlineColorNoBgRule extends AbstractRule
{
    public function getId(): string { return 'inline-color-no-bg'; }
    public function getDescription(): string { return 'An inline color style without a corresponding background-color may cause contrast failures.'; }
    public function getCategory(): string { return 'Images'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '1.4.3 Contrast (Minimum)'; }
    public function getFixHint(): string { return 'Add an explicit background-color alongside the color declaration.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $style = $attrs['style'] ?? '';
                if (preg_match('/\bcolor\s*:/i', $style) && !preg_match('/background(-color)?\s*:/i', $style)) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        'Inline color without background-color may cause contrast issues.');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
