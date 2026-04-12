<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ButtonEmptyRule extends AbstractRule
{
    public function getId(): string
    {
        return 'button-empty';
    }

    public function getDescription(): string
    {
        return 'Buttons must have discernible text.';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getWcagCriteria(): string
    {
        return '4.1.2 Name, Role, Value';
    }

    public function getFixHint(): string
    {
        return 'Add text content or an aria-label to the <button>.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'button') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);
                $hasText = $this->hasVisibleText($node);

                if (!$hasAriaLabel && !$hasText) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<button> has no discernible text and no accessible label.',
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

    private function hasVisibleText(array $node): bool
    {
        if (($node['nodeType'] ?? '') === 'Text' && trim($node['content'] ?? '') !== '') {
            return true;
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                if ($this->hasVisibleText($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}
