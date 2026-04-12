<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;
use MalikAd778\BladeAlly\Support\GenericLabelList;

class LinkGenericLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'link-generic-label';
    }

    public function getDescription(): string
    {
        return 'Links should not use non-descriptive generic text like "click here".';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getDefaultSeverity(): string
    {
        return 'warning';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.4 Link Purpose (In Context)';
    }

    public function getFixHint(): string
    {
        return 'Use descriptive text for the link that makes sense out of context.';
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

                $text = $this->extractVisibleText($node);
                
                if ($text !== '' && GenericLabelList::isGeneric($text)) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        "Link text '{$text}' is too generic. Screen reader users navigating by links need descriptive context.",
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

    private function extractVisibleText(array $node): string
    {
        $text = '';
        
        if (($node['nodeType'] ?? '') === 'Text') {
            $text .= $node['value'] ?? '';
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $text .= ' ' . $this->extractVisibleText($child);
            }
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
