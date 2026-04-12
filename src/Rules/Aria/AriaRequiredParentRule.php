<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaRequiredParentRule extends AbstractRule
{
    public function getId(): string { return 'aria-required-parent'; }
    public function getDescription(): string { return 'Certain ARIA roles must be contained within a specific parent role.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.3.1 Info and Relationships'; }
    public function getFixHint(): string { return 'Wrap the element in the required parent role (e.g. role="listitem" must be inside role="list").'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $requiredParents = [
            'listitem'          => ['list'],
            'option'            => ['listbox', 'select'],
            'menuitem'          => ['menu', 'menubar'],
            'menuitemcheckbox'  => ['menu', 'menubar'],
            'menuitemradio'     => ['menu', 'menubar'],
            'tab'               => ['tablist'],
            'treeitem'          => ['tree', 'group'],
            'row'               => ['grid', 'rowgroup', 'table', 'treegrid'],
            'gridcell'          => ['row'],
            'columnheader'      => ['row'],
            'rowheader'         => ['row'],
        ];
        $this->checkRequiredParents($ast, $context, $requiredParents, [], $violations);
        return $violations;
    }

    private function checkRequiredParents(array $ast, $context, array $requiredParents, array $parentRoles, array &$violations): void
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role  = strtolower($attrs['role'] ?? '');
                if ($role && isset($requiredParents[$role])) {
                    $allowed = $requiredParents[$role];
                    if (empty(array_intersect($allowed, $parentRoles))) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            "role=\"{$role}\" must be contained within: " . implode(', ', $allowed) . '.');
                    }
                }
                $newParents = array_merge($parentRoles, $role ? [$role] : []);
                $this->checkRequiredParents($node['children'] ?? [], $context, $requiredParents, $newParents, $violations);
            }
        }
    }
}
