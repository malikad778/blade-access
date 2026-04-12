<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ImgAltTooLongRule extends AbstractRule
{
    public function getId(): string
    {
        return 'img-alt-too-long';
    }

    public function getDescription(): string
    {
        return 'Alt text should be concise.';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getWcagCriteria(): string
    {
        return '1.1.1 Non-text Content';
    }

    public function getFixHint(): string
    {
        return 'Keep alt text under 150 characters. Use adjacent text for longer descriptions.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        $maxLength = $context->getRuleConfig($this->getId())['max_length'] ?? 150;

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $alt = $attrs['alt'] ?? '';
                
                if (strlen($alt) > $maxLength) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        "<img> alt attribute is too long (exceeds {$maxLength} characters).",
                        $this->getDefaultSeverity(),
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
