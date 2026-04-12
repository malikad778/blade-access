<?php

namespace MalikAd778\BladeAlly\Rules\Headings;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HeadingEmptyRule extends AbstractRule
{
    public function getId(): string
    {
        return 'heading-empty';
    }

    public function getDescription(): string
    {
        return 'Heading elements must contain descriptive text.';
    }

    public function getCategory(): string
    {
        return 'Headings';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.6 Headings and Labels';
    }

    public function getFixHint(): string
    {
        return 'Ensure the heading tag has text or an image with alt text inside it.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && in_array(strtolower($node['tagName'] ?? ''), $headings, true)) {
                if (!$this->hasVisibleText($node)) {
                    $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                    $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);

                    if (!$hasAriaLabel) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            "Empty <{$node['tagName']}> found.",
                            $this->getDefaultSeverity(),
                            $this->getFixHint()
                        );
                    }
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
        if (($node['nodeType'] ?? '') === 'Text' && trim($node['value'] ?? '') !== '') {
            return true;
        }

        if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
            $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
            if (isset($attrs['alt']) && trim($attrs['alt']) !== '') {
                return true;
            }
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
