<?php

namespace MalikAd778\BladeAlly\Rules\Tables;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class TableThScopeRule extends AbstractRule
{
    public function getId(): string
    {
        return 'table-th-scope';
    }

    public function getDescription(): string
    {
        return 'Table Header (th) must have a scope attribute to associate header cells and data cells.';
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
        return 'Use scope="col" for column headers or scope="row" for row headers.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'table') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role = strtolower($attrs['role'] ?? '');

                if ($role === 'presentation' || $role === 'none') {
                    continue;
                }

                $violations = array_merge($violations, $this->checkTableHeaders($node['children'] ?? [], $context));
            } elseif (!empty($node['children'])) {
                
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }

        return $violations;
    }

    private function checkTableHeaders(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            $isElement = ($node['nodeType'] ?? '') === 'Element';
            $tagName = strtolower($node['tagName'] ?? '');

            if ($isElement && $tagName === 'table') {
                
                $violations = array_merge($violations, $this->check([$node], $context));
                continue;
            }

            if ($isElement && $tagName === 'th') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (!isset($attrs['scope']) || !in_array(strtolower(trim($attrs['scope'])), ['row', 'col', 'rowgroup', 'colgroup'], true)) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Table header <th> lacks a valid "scope" attribute.',
                        $this->getDefaultSeverity(),
                        $this->getFixHint()
                    );
                }
            }

            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->checkTableHeaders($node['children'], $context));
            }
        }

        return $violations;
    }
}
