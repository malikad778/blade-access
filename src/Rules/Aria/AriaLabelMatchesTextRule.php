<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaLabelMatchesTextRule extends AbstractRule
{
    public function getId(): string { return 'aria-label-matches-text'; }
    public function getDescription(): string { return 'aria-label should not duplicate the visible text content of the element.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '2.5.3 Label in Name'; }
    public function getFixHint(): string { return 'Remove aria-label if it duplicates visible text, or update it to add meaningful context.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['aria-label'])) {
                    $label      = strtolower(trim($attrs['aria-label']));
                    $innerText  = strtolower(trim($this->collectText($node)));
                    if ($label !== '' && $innerText !== '' && $label === $innerText) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            'aria-label duplicates the visible text content of the element and is redundant.');
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function collectText(array $node): string
    {
        $text = '';
        foreach ($node['children'] ?? [] as $child) {
            if (($child['nodeType'] ?? '') === 'Text') { $text .= $child['content'] ?? ''; }
            elseif (($child['nodeType'] ?? '') === 'Element') { $text .= $this->collectText($child); }
        }
        return $text;
    }
}
