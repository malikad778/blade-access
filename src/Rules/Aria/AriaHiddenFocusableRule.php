<?php

namespace MalikAd778\BladeAlly\Rules\Aria;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AriaHiddenFocusableRule extends AbstractRule
{
    public function getId(): string { return 'aria-hidden-focusable'; }
    public function getDescription(): string { return 'Elements with aria-hidden="true" must not contain focusable elements.'; }
    public function getCategory(): string { return 'Aria'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '4.1.2 Name, Role, Value'; }
    public function getFixHint(): string { return 'Remove aria-hidden="true" if the element must be focusable, or add tabindex="-1" to the focusable child.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') !== 'Element') {
                continue;
            }
            $attrs      = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
            $ariaHidden = isset($attrs['aria-hidden']) && strtolower(trim($attrs['aria-hidden'])) === 'true';

            if ($ariaHidden) {
                if (!empty($this->findFocusableElements($node))) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Element has aria-hidden="true" but contains focusable elements.',
                    );
                }
            } elseif (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

    private function findFocusableElements(array $node): array
    {
        $focusable     = [];
        $focusableTags = ['a', 'button', 'input', 'select', 'textarea', 'iframe', 'summary'];
        $attrs         = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
        $tag           = strtolower($node['tagName'] ?? '');

        if (isset($attrs['tabindex']) && (int) $attrs['tabindex'] >= 0) {
            $focusable[] = $node;
        } elseif (in_array($tag, $focusableTags, true)) {
            if ($tag === 'a' && !isset($attrs['href'])) {
            } elseif (isset($attrs['disabled'])) {
            } else {
                $focusable[] = $node;
            }
        }

        foreach ($node['children'] ?? [] as $child) {
            if (($child['nodeType'] ?? '') === 'Element') {
                $focusable = array_merge($focusable, $this->findFocusableElements($child));
            }
        }

        return $focusable;
    }
}
