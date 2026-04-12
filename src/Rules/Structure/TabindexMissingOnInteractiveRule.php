<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class TabindexMissingOnInteractiveRule extends AbstractRule
{
    public function getId(): string { return 'tabindex-missing-on-interactive'; }
    public function getDescription(): string { return 'Custom interactive elements (role="button", role="tab", etc.) must have tabindex="0".'; }
    public function getCategory(): string { return 'Structure'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '2.1.1 Keyboard'; }
    public function getFixHint(): string { return 'Add tabindex="0" to custom interactive elements so keyboard users can reach them.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $interactiveRoles = ['button', 'tab', 'menuitem', 'option', 'radio', 'checkbox', 'switch', 'link'];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag   = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role  = strtolower($attrs['role'] ?? '');
                $nativeInteractive = in_array($tag, ['a', 'button', 'input', 'select', 'textarea'], true);
                if ($role && in_array($role, $interactiveRoles, true) && !$nativeInteractive) {
                    if (!isset($attrs['tabindex'])) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "Element with role="{$role}" is missing tabindex="0".");
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
