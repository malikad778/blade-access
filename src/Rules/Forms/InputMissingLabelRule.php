<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class InputMissingLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'input-missing-label';
    }

    public function getDescription(): string
    {
        return 'Input elements must have an associated accessible label.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'Provide a <label for="id"> or aria-label for the <input>.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        return $this->scan($ast, $context, false);
    }

    private function scan(array $ast, RuleContext $context, bool $insideLabel): array
    {
        $violations = [];

        foreach ($ast as $node) {
            $isLabel = (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'label');
            $currentInsideLabel = $insideLabel || $isLabel;

            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'input') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $type = strtolower($attrs['type'] ?? 'text');
                
                if (in_array($type, ['hidden', 'submit', 'reset', 'button', 'image'])) {
                    continue; 
                }

                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);
                $hasId = isset($attrs['id']);

                if (!$hasAriaLabel && !$currentInsideLabel) {
                    $hasForLabel = false;
                    if ($hasId) {
                        $hasForLabel = $this->hasMatchingLabelFor($context->ast, $attrs['id']);
                    }
                    
                    if (!$hasForLabel) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            '<input> is missing an accessible label (<label for="...">, an enclosing <label>, aria-label, or aria-labelledby).',
                            $this->getDefaultSeverity(),
                            $this->getFixHint()
                        );
                    }
                }
            }
            
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->scan($node['children'], $context, $currentInsideLabel));
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
