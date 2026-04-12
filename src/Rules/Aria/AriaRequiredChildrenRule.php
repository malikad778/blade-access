<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaRequiredChildrenRule extends AbstractRule
{
    public function getId(): string { return 'aria-required-children'; }
    public function getDescription(): string { return 'Certain ARIA roles require specific child roles to be present.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Add the required child roles (e.g. role="listitem" inside role="list").'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $requiredChildren = [
            'list'     => ['listitem'],
            'listbox'  => ['option'],
            'menu'     => ['menuitem', 'menuitemcheckbox', 'menuitemradio'],
            'menubar'  => ['menuitem', 'menuitemcheckbox', 'menuitemradio'],
            'tablist'  => ['tab'],
            'tree'     => ['treeitem'],
            'grid'     => ['row'],
            'rowgroup' => ['row'],
            'row'      => ['cell', 'gridcell', 'columnheader', 'rowheader'],
        ];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role  = strtolower($attrs['role'] ?? '');
                if ($role && isset($requiredChildren[$role])) {
                    $childRoles = $this->collectChildRoles($node['children'] ?? []);
                    $required   = $requiredChildren[$role];
                    $found      = array_intersect($required, $childRoles);
                    if (empty($found) && !empty($node['children'])) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "role="{$role}" requires child role(s): " . implode(', ', $required) . '.');
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function collectChildRoles(array $children): array
    {
        $roles = [];
        foreach ($children as $child) {
            if (($child['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($child['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['role'])) { $roles[] = strtolower($attrs['role']); }
                $roles = array_merge($roles, $this->collectChildRoles($child['children'] ?? []));
            }
        }
        return $roles;
    }
}
