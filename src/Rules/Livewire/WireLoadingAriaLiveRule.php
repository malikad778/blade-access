<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class WireLoadingAriaLiveRule extends AbstractRule
{
    public function getId(): string
    {
        return 'wire-loading-aria-live';
    }

    public function getDescription(): string
    {
        return 'Livewire loading indicators should use aria-live regions or be programmatically announced.';
    }

    public function getCategory(): string
    {
        return 'Livewire';
    }

    public function getWcagCriteria(): string
    {
        return '4.1.3 Status Messages';
    }

    public function getFixHint(): string
    {
        return 'Combine wire:loading with aria-live="polite" to notify screen readers of status changes.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);

                if (isset($attrs['wire:loading'])) {
                    $hasAriaLive = isset($attrs['aria-live']) || (isset($attrs['role']) && in_array(strtolower($attrs['role']), ['status', 'alert', 'progressbar']));

                    if (!$hasAriaLive && !isset($attrs['wire:target'])) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Element uses wire:loading but lacks aria-live="polite". Screen readers may ignore visual loading states.',
                            'info',
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
