<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class IconButtonLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'icon-button-label';
    }

    public function getDescription(): string
    {
        return 'Buttons containing only icons must have accessible labels.';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getWcagCriteria(): string
    {
        return '1.1.1 Non-text Content';
    }

    public function getFixHint(): string
    {
        return 'Add aria-label to the <button> or visible hidden text inside it.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'button') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);
                
                $hasText = false;
                $hasIcon = false;

                if (!empty($node['children'])) {
                    foreach ($node['children'] as $child) {
                        if (($child['nodeType'] ?? '') === 'Text' && trim($child['value'] ?? '') !== '') {
                            $hasText = true;
                        }
                        if (($child['nodeType'] ?? '') === 'Element' && in_array(strtolower($child['tagName'] ?? ''), ['svg', 'i'])) {
                            $hasIcon = true;
                        }
                    }
                }

                if ($hasIcon && !$hasText && !$hasAriaLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Icon-only <button> is missing an accessible label.',
                        $this->getDefaultSeverity(),
                        $this->getFixHint()
                    );
                }
            }
            
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }

        return $violations;
    }
}
