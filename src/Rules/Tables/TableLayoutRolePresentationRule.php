<?php

namespace MalikAd778\BladeAlly\Rules\Tables;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class TableLayoutRolePresentationRule extends AbstractRule
{
    public function getId(): string
    {
        return 'table-layout-role-presentation';
    }

    public function getDescription(): string
    {
        return 'Tables strictly for layout should have role="presentation".';
    }

    public function getCategory(): string
    {
        return 'Tables';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'If the table is just for styling a grid, ensure role="presentation" is on the <table> element.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'table') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role = strtolower($attrs['role'] ?? '');

                if ($role !== 'presentation' && $role !== 'none') {
                    if (!$this->hasDataElements($node['children'] ?? [])) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Table appears to have no headers (th, thead) or captions. If it is for layout, add role="presentation".',
                            'warning',
                            $this->getFixHint()
                        );
                    }
                }
            }

            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }

        return $violations;
    }

    private function hasDataElements(array $ast): bool
    {
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tag = strtolower($node['tagName'] ?? '');
                if (in_array($tag, ['th', 'thead', 'tfoot', 'caption'], true)) {
                    return true;
                }
            }
            if (!empty($node['children'])) {
                if ($this->hasDataElements($node['children'])) {
                    return true;
                }
            }
        }
        return false;
    }
}
