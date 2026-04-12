<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class WireClickKeyboardRule extends AbstractRule
{
    public function getId(): string
    {
        return 'wire-click-keyboard';
    }

    public function getDescription(): string
    {
        return 'Elements with wire:click must be keyboard accessible or have a matching wire:keydown event.';
    }

    public function getCategory(): string
    {
        return 'Livewire';
    }

    public function getWcagCriteria(): string
    {
        return '2.1.1 Keyboard';
    }

    public function getFixHint(): string
    {
        return 'Use a <button> for wire:click or add wire:keydown.enter to support keyboard users.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        $nativeFocusable = ['a', 'button', 'input', 'select', 'textarea', 'summary'];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tagName = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);

                if (isset($attrs['wire:click']) || isset($attrs['wire:click.prevent'])) {
                    if (!in_array($tagName, $nativeFocusable, true)) {
                        $hasKeydown = isset($attrs['wire:keydown.enter']) || isset($attrs['wire:keydown']) || isset($attrs['@keydown.enter']);

                        if (!$hasKeydown) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                "Non-interactive element <{$tagName}> uses wire:click without a keyboard alternative like wire:keydown.enter.",
                                $this->getDefaultSeverity(),
                                $this->getFixHint()
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
}
