<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HardcodedColorValueRule extends AbstractRule
{
    public function getId(): string { return 'hardcoded-color-value'; }
    public function getDescription(): string { return 'Hardcoded hex/rgb color values in inline styles should be reviewed for contrast compliance.'; }
    public function getCategory(): string { return 'Images'; }
    public function getDefaultSeverity(): string { return 'info'; }
    public function getWcagCriteria(): string { return '1.4.3 Contrast (Minimum)'; }
    public function getFixHint(): string { return 'Replace hardcoded colors with design tokens, or verify contrast ratio meets 4.5:1.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $style = $attrs['style'] ?? '';
                if (preg_match('/(color|background(-color)?)\s*:\s*(#[0-9a-fA-F]{3,6}|rgb\()/i', $style)) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        'Hardcoded color value found in inline style. Verify contrast ratio.', 'info');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
