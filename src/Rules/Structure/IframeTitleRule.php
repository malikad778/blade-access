<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class IframeTitleRule extends AbstractRule
{
    public function getId(): string
    {
        return 'iframe-title';
    }

    public function getDescription(): string
    {
        return 'Inline frames (iframes) must have a title attribute to describe their contents.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.1 Bypass Blocks';
    }

    public function getFixHint(): string
    {
        return 'Add a title="description of iframe content" attribute to the <iframe>.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'iframe') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $hasTitle = isset($attrs['title']) && trim($attrs['title']) !== '';
                $hasAriaLabel = isset($attrs['aria-label']) && trim($attrs['aria-label']) !== '';

                if (!$hasTitle && !$hasAriaLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<iframe> is missing a descriptive title attribute.',
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
