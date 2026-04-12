<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class InputPlaceholderAsLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'input-placeholder-as-label';
    }

    public function getDescription(): string
    {
        return 'Placeholders should not be used as the only accessible label.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '3.3.2 Labels or Instructions';
    }

    public function getFixHint(): string
    {
        return 'Provide a visible <label> element or aria-label; do not rely solely on the placeholder.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'input') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $type = strtolower($attrs['type'] ?? 'text');

                if (in_array($type, ['hidden', 'submit', 'reset', 'button', 'image'])) {
                    continue;
                }

                $hasPlaceholder = isset($attrs['placeholder']) && trim($attrs['placeholder']) !== '';
                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);
                
                $hasForLabel = false;
                if (isset($attrs['id'])) {
                    $hasForLabel = $this->hasMatchingLabelFor($context->ast, $attrs['id']);
                }

                if ($hasPlaceholder && !$hasAriaLabel && !$hasForLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<input> uses a placeholder but lacks a proper accessible label.',
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

    private function hasMatchingLabelFor(array $ast, string $targetId): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'label') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['for']) && $attrs['for'] === $targetId) {
                    return true;
                }
            }
            if (!empty($node['children'])) {
                if ($this->hasMatchingLabelFor($node['children'], $targetId)) {
                    return true;
                }
            }
        }
        return false;
    }
}
