<?php

namespace MalikAd778\BladeAlly\Rules\Dialogs;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class DialogLabelRule extends AbstractRule
{
    public function getId(): string
    {
        return 'dialog-label';
    }

    public function getDescription(): string
    {
        return 'Dialogs should have an accessible name.';
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
        return 'Provide aria-label, aria-labelledby on the dialog/modal element.';
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
                    $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);

                    if (!$hasAriaLabel) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Dialog lacks an accessible name (aria-label or aria-labelledby).',
                            $this->getDefaultSeverity(),
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
