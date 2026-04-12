<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class FocusVisibleSuppressedRule extends AbstractRule
{
    public function getId(): string { return 'focus-visible-suppressed'; }
    public function getDescription(): string { return 'outline:none or outline:0 in inline styles removes the focus indicator.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '2.4.7 Focus Visible'; }
    public function getFixHint(): string { return 'Remove outline:none, or replace it with a visible custom focus style.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $style = $attrs['style'] ?? '';
                if (preg_match('/outline\s*:\s*0|outline\s*:\s*none/i', $style)) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        'Inline style suppresses focus outline. This hides the keyboard focus indicator.');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
