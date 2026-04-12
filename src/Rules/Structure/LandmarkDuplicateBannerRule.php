<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LandmarkDuplicateBannerRule extends AbstractRule
{
    public function getId(): string { return 'landmark-duplicate-banner'; }
    public function getDescription(): string { return 'Multiple <header> or role="banner" elements should not appear at the page level.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Ensure only one top-level <header> or role="banner" exists. Nested headers inside <article>/<section> are fine.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $banners = $this->collectBanners($ast);
        if (count($banners) > 1) {
            return [$this->makeViolation($context->filePath,
                $banners[1]['line'] ?? 1, $banners[1]['column'] ?? 1,
                'Multiple banner landmarks (<header> or role="banner") found at the page level.')];
        }
        return [];
    }

    private function collectBanners(array $ast): array
    {
        $banners = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag   = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role  = strtolower($attrs['role'] ?? '');
                if ($tag === 'header' || $role === 'banner') { $banners[] = $node; }
                if (!empty($node['children'])) { $banners = array_merge($banners, $this->collectBanners($node['children'])); }
            }
        }
        return $banners;
    }
}
