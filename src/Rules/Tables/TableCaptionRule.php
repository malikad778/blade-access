<?php

namespace MalikAd778\BladeAlly\Rules\Tables;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class TableCaptionRule extends AbstractRule
{
    public function getId(): string
    {
        return 'table-caption';
    }

    public function getDescription(): string
    {
        return 'Data tables should have a caption element or aria-label.';
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
        return 'Add a <caption> element as the first child of the <table> or use an aria-label.';
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

                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);
                $hasCaption = false;

                if (!empty($node['children'])) {
                    foreach ($node['children'] as $child) {
                        if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === 'caption') {
                            $hasCaption = true;
                            break;
                        }
                    }
                }

                if (!$hasCaption && !$hasAriaLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Data <table> lacks a <caption> or aria-label.',
                        $this->getDefaultSeverity(),
                        $this->getFixHint()
                    );
                }
            }

            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }

        return $violations;
    }
}
