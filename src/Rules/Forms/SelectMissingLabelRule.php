<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class SelectMissingLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'select-missing-label';
    }

    public function getDescription(): string
    {
        return 'Select elements must have accessible labels.';
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
        return 'Add a <label> with a "for" attribute matching the select\'s "id", or use aria-label/aria-labelledby.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'select') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);

                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);
                $hasImplicitLabel = $this->isInsideLabel($ast, $node);
                
                $hasExplicitLabel = false;
                if (isset($attrs['id']) && trim($attrs['id']) !== '') {
                    $hasExplicitLabel = $this->hasMatchingLabelFor($context->ast, trim($attrs['id']));
                }

                if (!$hasAriaLabel && !$hasImplicitLabel && !$hasExplicitLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<select> element is missing an accessible label.',
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

    private function isInsideLabel(array $ast, array $targetNode, bool $inLabel = false): bool
    {
        foreach ($ast as $node) {
            $currentInLabel = $inLabel || (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'label');
            
            if ($node === $targetNode && $currentInLabel) {
                return true;
            }

            if (!empty($node['children'])) {
                if ($this->isInsideLabel($node['children'], $targetNode, $currentInLabel)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasMatchingLabelFor(array $ast, string $targetId): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'label') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['for']) && trim($attrs['for']) === $targetId) {
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
