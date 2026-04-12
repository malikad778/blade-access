<?php

namespace MalikAd778\BladeAlly\Rules\Dialogs;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class DialogAriaModalRule extends AbstractRule
{
    public function getId(): string
    {
        return 'dialog-aria-modal';
    }

    public function getDescription(): string
    {
        return 'Modal dialogs must have aria-modal="true" to inform screen readers of the focus trap.';
    }

    public function getCategory(): string
    {
        return 'Dialogs';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.1 Info and Relationships';
    }

    public function getFixHint(): string
    {
        return 'Ensure modal overlays have aria-modal="true" so screen readers constrain reading outside the modal.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $tagName = strtolower($node['tagName'] ?? '');
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $role = strtolower($attrs['role'] ?? '');

                if ($tagName === 'dialog' || $role === 'dialog' || $role === 'alertdialog') {
                    $hasAriaModal = isset($attrs['aria-modal']) && strtolower(trim($attrs['aria-modal'])) === 'true';

                    if (!$hasAriaModal) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Dialog lacks aria-modal="true". If it is meant to trap focus naturally, this attribute is required.',
                            'warning',
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
