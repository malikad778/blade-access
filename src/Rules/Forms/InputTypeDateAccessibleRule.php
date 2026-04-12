<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class InputTypeDateAccessibleRule extends AbstractRule
{
    public function getId(): string
    {
        return 'input-type-date-accessible';
    }

    public function getDescription(): string
    {
        return 'Date inputs may be inaccessible on older browsers and devices. Use clear instructions.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'Date inputs (type="date") should ideally state expected formatting in the label or aria-describedby for contexts where the native picker fails.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'input') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $type = strtolower($attrs['type'] ?? 'text');

                if ($type === 'date') {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Input of type="date" used. Verify that fallback parsing rules or formatting hints are provided for users lacking a date picker interface.',
                        'info',
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
