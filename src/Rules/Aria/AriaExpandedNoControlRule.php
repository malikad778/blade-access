<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaExpandedNoControlRule extends AbstractRule
{
    public function getId(): string { return 'aria-expanded-no-control'; }
    public function getDescription(): string { return 'Elements with aria-expanded should control an associated element via aria-controls.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '4.1.2 Name, Role, Value'; }
    public function getFixHint(): string { return 'Add aria-controls pointing to the id of the element being expanded/collapsed.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['aria-expanded']) && !isset($attrs['aria-controls'])) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        'Element has aria-expanded but is missing aria-controls to identify the controlled element.');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
