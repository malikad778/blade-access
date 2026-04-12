<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaLabelledbyTargetMissingRule extends AbstractRule
{
    public function getId(): string { return 'aria-labelledby-target-missing'; }
    public function getDescription(): string { return 'aria-labelledby must reference an id that exists in the template.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Ensure the id referenced in aria-labelledby exists in the same template.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['aria-labelledby'])) {
                    foreach (preg_split('/\s+/', trim($attrs['aria-labelledby'])) as $target) {
                        if ($target && !str_contains($target, '{{') && !$this->idExistsInAst($context->ast, $target)) {
                            $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                                "aria-labelledby references id="{$target}" which was not found in this template.");
                        }
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
