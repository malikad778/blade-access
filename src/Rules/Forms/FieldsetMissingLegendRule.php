<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class FieldsetMissingLegendRule extends AbstractRule
{
    public function getId(): string
    {
        return 'fieldset-missing-legend';
    }

    public function getDescription(): string
    {
        return 'Fieldsets must contain a legend element.';
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
        return 'Add a <legend> as the first child of the <fieldset> to describe the grouped inputs.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'fieldset') {
                $hasLegend = false;
                
                if (!empty($node['children'])) {
                    foreach ($node['children'] as $child) {
                        if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === 'legend') {
                            $hasLegend = true;
                            break;
                        }
                    }
                }

                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);

                if (!$hasLegend && !$hasAriaLabel) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<fieldset> is missing a <legend> or accessible grouping label.',
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
