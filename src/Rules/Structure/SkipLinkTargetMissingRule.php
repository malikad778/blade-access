<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class SkipLinkTargetMissingRule extends AbstractRule
{
    public function getId(): string { return 'skip-link-target-missing'; }
    public function getDescription(): string { return 'A skip link href target must have a matching id in the template.'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '2.4.1 Bypass Blocks'; }
    public function getFixHint(): string { return 'Add id="main" (or matching id) to the target element of the skip link.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $href  = $attrs['href'] ?? '';
                if (str_starts_with($href, '#')) {
                    $targetId = ltrim($href, '#');
                    if ($targetId && !$this->idExistsInAst($context->ast, $targetId)) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "Skip link href="{$href}" references id="{$targetId}" which was not found in this template.");
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function idExistsInAst(array $ast, string $id): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (($attrs['id'] ?? '') === $id) { return true; }
            }
            if (!empty($node['children']) && $this->idExistsInAst($node['children'], $id)) { return true; }
        }
        return false;
    }
}
