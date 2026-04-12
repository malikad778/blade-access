<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class FormErrorMissingSuggestionRule extends AbstractRule
{
    public function getId(): string
    {
        return 'form-error-missing-suggestion';
    }

    public function getDescription(): string
    {
        return 'Form error messages should provide clear suggestions to fix the error.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '3.3.3 Error Suggestion';
    }

    public function getFixHint(): string
    {
        return 'If an error is automatically detected, the item\'s error message should ideally include a suggestion of how to fix it.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        $html = file_get_contents($context->filePath);
        if (str_contains($html, '@error') || preg_match('/class=".*(?:text-red-500|invalid-feedback|error).*"/', $html)) {
            $violations[] = $this->makeViolation(
                $context->filePath,
                1,
                1,
                'Form errors or red text blocks detected. Ensure they offer concrete suggestions for fixing the issue.',
                'info',
                $this->getFixHint()
            );
        }

        return $violations;
    }
}
