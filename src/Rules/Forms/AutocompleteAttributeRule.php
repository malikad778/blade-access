<?php

namespace MalikAd778\BladeAlly\Rules\Forms;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AutocompleteAttributeRule extends AbstractRule
{
    public function getId(): string
    {
        return 'autocomplete-attribute';
    }

    public function getDescription(): string
    {
        return 'Inputs gathering personal information should possess proper autocomplete attributes.';
    }

    public function getCategory(): string
    {
        return 'Forms';
    }

    public function getWcagCriteria(): string
    {
        return '1.3.5 Identify Input Purpose';
    }

    public function getFixHint(): string
    {
        return 'For fields expecting user personal data, define the appropriate autocomplete property (e.g., autocomplete="email").';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        
        $personalInputNamesPattern = '/(name|email|password|tel|phone|address|city|country|zip|postal|organization|company)/i';

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'input') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $type = strtolower($attrs['type'] ?? 'text');
                $name = strtolower($attrs['name'] ?? '');

                if (in_array($type, ['hidden', 'submit', 'button', 'reset', 'image', 'checkbox', 'radio'])) {
                    continue;
                }

                if (preg_match($personalInputNamesPattern, $name) && !isset($attrs['autocomplete'])) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        "Input with name '{$name}' could collect personal information but lacks an autocomplete attribute.",
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
