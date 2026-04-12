<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LivewirePollNoPauseRule extends AbstractRule
{
    public function getId(): string { return 'livewire-poll-no-pause'; }
    public function getDescription(): string { return 'wire:poll without wire:poll.visible continues polling even when off-screen, harming accessibility and performance.'; }
    public function getCategory(): string { return 'Livewire'; }
    public function getDefaultSeverity(): string { return 'warning'; }
    public function getWcagCriteria(): string { return '2.2.2 Pause, Stop, Hide'; }
    public function getFixHint(): string { return 'Replace wire:poll with wire:poll.visible to pause when not visible.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                foreach (array_keys($attrs) as $key) {
                    if (str_starts_with($key, 'wire:poll') && !str_contains($key, '.visible')) {
                        $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                            'wire:poll without .visible modifier keeps polling when hidden. Use wire:poll.visible.');
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
