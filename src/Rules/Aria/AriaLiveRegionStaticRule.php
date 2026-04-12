<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaLiveRegionStaticRule extends AbstractRule
{
    public function getId(): string { return 'aria-live-region-static'; }
    public function getDescription(): string { return 'aria-live regions should contain dynamic content, not static text that never changes.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '4.1.3 Status Messages'; }
    public function getFixHint(): string { return 'Remove aria-live from elements with static content, or ensure content is updated dynamically.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['aria-live'])) {
                    $hasDynamic = isset($attrs['wire:model']) || isset($attrs['wire:poll'])
                        || isset($attrs['x-text']) || isset($attrs['x-html'])
                        || $this->hasBladeOutput($node);
                    if (!$hasDynamic && $this->hasOnlyStaticText($node)) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            'aria-live region appears to contain only static content.');
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function hasBladeOutput(array $node): bool
    {
        foreach ($node['children'] ?? [] as $child) {
            if (($child['nodeType'] ?? '') === 'BladeDirective') { return true; }
            if (!empty($child['children']) && $this->hasBladeOutput($child)) { return true; }
        }
        return false;
    }

    private function hasOnlyStaticText(array $node): bool
    {
        foreach ($node['children'] ?? [] as $child) {
            if (($child['nodeType'] ?? '') === 'BladeDirective') { return false; }
            if (($child['nodeType'] ?? '') === 'Element') { return false; }
        }
        return true;
    }
}
