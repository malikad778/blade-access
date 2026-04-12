<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LandmarkDuplicateMainRule extends AbstractRule
{
    public function getId(): string { return 'landmark-duplicate-main'; }
    public function getDescription(): string { return 'Only one <main> landmark should be visible at a time.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Ensure only one <main> element is present, or hide inactive ones with hidden attribute.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $mains = $this->collectMains($ast);
        if (count($mains) > 1) {
            return [$this->makeViolation($context->filePath,
                $mains[1]['line'] ?? 1, $mains[1]['column'] ?? 1,
                'Multiple <main> landmarks found. Only one should be active per page.')];
        }
        return [];
    }

    private function collectMains(array $ast): array
    {
        $mains = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag   = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role  = strtolower($attrs['role'] ?? '');
                if ($tag === 'main' || $role === 'main') { $mains[] = $node; }
                if (!empty($node['children'])) { $mains = array_merge($mains, $this->collectMains($node['children'])); }
            }
        }
        return $mains;
    }
}
