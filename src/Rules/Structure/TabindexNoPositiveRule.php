<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class TabindexNoPositiveRule extends AbstractRule
{
    public function getId(): string
    {
        return 'tabindex-no-positive';
    }

    public function getDescription(): string
    {
        return 'Avoid using positive tabindex values as they disrupt the natural reading order.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '2.4.3 Focus Order';
    }

    public function getFixHint(): string
    {
        return 'Use tabindex="0" or tabindex="-1" instead of positive values.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (isset($attrs['tabindex']) && is_numeric($attrs['tabindex'])) {
                    if ((int)$attrs['tabindex'] > 0) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Element uses a positive tabindex (' . $attrs['tabindex'] . '), which disrupts logical navigation.',
                            $this->getDefaultSeverity(),
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
}
