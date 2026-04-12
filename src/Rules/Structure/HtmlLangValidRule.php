<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;
use MalikAd778\BladeAlly\Support\Bcp47LanguageCodes;

class HtmlLangValidRule extends AbstractRule
{
    public function getId(): string
    {
        return 'html-lang-valid';
    }

    public function getDescription(): string
    {
        return 'The <html> element must have a valid lang attribute.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '3.1.1 Language of Page';
    }

    public function getFixHint(): string
    {
        return 'Set a valid BCP 47 language code on the <html> tag (e.g. lang="en").';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'html') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (!isset($attrs['lang']) || trim($attrs['lang']) === '') {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'The <html> tag is missing a lang attribute.',
                        $this->getDefaultSeverity(),
                        $this->getFixHint()
                    );
                } else {
                    $lang = trim($attrs['lang']);
                    if (!str_contains($lang, '{{') && !str_contains($lang, '$')) {
                        if (!Bcp47LanguageCodes::isValid($lang)) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                "The <html> tag uses an invalid language code '{$lang}'.",
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
