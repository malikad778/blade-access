<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class WireTargetMatchRule extends AbstractRule
{
    public function getId(): string { return 'wire-target-match'; }
    public function getDescription(): string { return 'Elements with wire:target should correspond to an active action.'; }
    public function getCategory(): string { return 'Livewire'; }
    public function getWcagCriteria(): string { return '4.1.1 Parsing'; }
    public function getFixHint(): string { return 'Ensure the wire:target action string matches an existing wire:click or wire:submit.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $actions    = $this->collectLivewireActions($ast);

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['wire:target'])) {
                    foreach (explode(',', $attrs['wire:target']) as $target) {
                        $target = trim($target);
                        if ($target !== '' && !str_starts_with($target, '$') && !in_array($target, $actions, true)) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                "wire:target=\"{$target}\" does not seem to match any declared wire:click or wire:submit action.",
                                'info',
                            );
                        }
                    }
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function collectLivewireActions(array $ast): array
    {
        $actions = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                foreach ($attrs as $key => $val) {
                    if (str_starts_with($key, 'wire:click') || str_starts_with($key, 'wire:submit')) {
                        $clean = trim(preg_replace('/\(.*\)/', '', (string) $val));
                        if ($clean !== '') {
                            $actions[] = $clean;
                        }
                    }
                }
            }
            if (!empty($node['children'])) {
                $actions = array_merge($actions, $this->collectLivewireActions($node['children']));
            }
        }
        return array_unique($actions);
    }
}
