<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class HtmlLangMissingRule extends AbstractRule
{
    public function getId(): string { return 'html-lang-missing'; }
    public function getDescription(): string { return '<html> element must have a lang attribute.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '3.1.1 Language of Page'; }
    public function getFixHint(): string { return 'Add lang="en" (or appropriate BCP47 code) to the <html> tag.'; }

    public function check(array $ast, RuleContext $context): array
    {
        if (!$context->isLayoutTemplate()) {
            return [];
        }
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'html') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (!isset($attrs['lang']) || trim($attrs['lang']) === '') {
                    return [$this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        'The <html> element is missing a lang attribute.')];
                }
                return [];
            }
        }
        return [];
    }

}
