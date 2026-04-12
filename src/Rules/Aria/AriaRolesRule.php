<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;
use MalikAd778\BladeAlly\Support\AriaRoleDefinitions;

class AriaRolesRule extends AbstractRule
{
    public function getId(): string
    {
        return 'aria-roles';
    }

    public function getDescription(): string
    {
        return 'Elements with an ARIA role must use a valid, non-abstract ARIA role.';
    }

    public function getCategory(): string
    {
        return 'Aria';
    }

    public function getWcagCriteria(): string
    {
        return '4.1.2 Name, Role, Value';
    }

    public function getFixHint(): string
    {
        return 'Provide a valid WAI-ARIA role value (e.g. role="button"). Avoid abstract roles or typos.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (isset($attrs['role'])) {
                    $roles = array_filter(explode(' ', $attrs['role']));
                    
                    foreach ($roles as $role) {
                        $role = trim($role);
                        if (str_contains($role, '{{') || str_contains($role, '$')) {
                            continue; 
                        }

                        if (!AriaRoleDefinitions::isValidRole($role)) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                "Invalid ARIA role '{$role}' detected.",
                                $this->getDefaultSeverity(),
                                $this->getFixHint()
                            );
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
}
