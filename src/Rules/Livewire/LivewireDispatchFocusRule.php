<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LivewireDispatchFocusRule extends AbstractRule
{
    public function getId(): string { return 'livewire-dispatch-focus'; }
    public function getDescription(): string { return '$dispatch events that open UI should include focus management.'; }
    public function getCategory(): string { return 'Livewire'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '2.4.3 Focus Order'; }
    public function getFixHint(): string { return 'After dispatching an event that opens a modal or panel, move focus to the opened element.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                foreach ($attrs as $key => $val) {
                    if (str_contains((string) $val, '$dispatch') || str_contains((string) $val, 'this.$dispatch')) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            '$dispatch detected. Ensure any UI opened by this event has focus moved to it.', 'warning');
                        break;
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
