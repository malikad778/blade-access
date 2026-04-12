<?php

namespace MalikAd778\BladeAlly\Rules\Livewire;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class WireNavigateLinkRule extends AbstractRule
{
    public function getId(): string
    {
        return 'wire-navigate-link';
    }

    public function getDescription(): string
    {
        return 'wire:navigate should primarily be used on anchors (<a>).';
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
        return 'Move wire:navigate to an <a> tag, or ensure the current element is properly accessible as a link.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tagName = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);

                if (isset($attrs['wire:navigate']) && $tagName !== 'a') {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        "wire:navigate found on <{$tagName}>. It is best practice to use wire:navigate on native <a> tags for robust keyboard and screen-reader support.",
                        'warning',
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
