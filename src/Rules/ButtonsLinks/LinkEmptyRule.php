<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LinkEmptyRule extends AbstractRule
{
    public function getId(): string
    {
        return 'link-empty';
    }

    public function getDescription(): string
    {
        return 'Links must have discernible text.';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.4 Link Purpose (In Context)';
    }

    public function getFixHint(): string
    {
        return 'Add text content or an aria-label to the <a> tag.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (!isset($attrs['href'])) {
                    continue;
                }

                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);
                $hasText = $this->hasVisibleText($node);

                if (!$hasAriaLabel && !$hasText) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<a href> element has no discernible text or accessible name.',
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
