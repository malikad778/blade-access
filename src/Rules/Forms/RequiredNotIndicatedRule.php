<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class RequiredNotIndicatedRule extends AbstractRule
{
    public function getId(): string
    {
        return 'required-not-indicated';
    }

    public function getDescription(): string
    {
        return 'Required inputs must be visually and programmatically indicated.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '3.3.2 Labels or Instructions';
    }

    public function getFixHint(): string
    {
        return 'Ensure `required` attribute is set, or use `aria-required="true"` along with a visual text marker in the label.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && in_array(strtolower($node['tagName'] ?? ''), ['input', 'select', 'textarea'], true)) {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                $htmlRequired = array_key_exists('required', $attrs) && $attrs['required'] !== 'false';
                $ariaRequired = isset($attrs['aria-required']) && strtolower($attrs['aria-required']) === 'true';

                $isVisuallyIndicated = true;

                if ($htmlRequired || $ariaRequired) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'Currently checking programmatic requirement, ensure your visual label indicates this field is required.',
                        'info',
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
