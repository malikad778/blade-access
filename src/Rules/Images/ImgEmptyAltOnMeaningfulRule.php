<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ImgEmptyAltOnMeaningfulRule extends AbstractRule
{
    public function getId(): string
    {
        return 'img-empty-alt-on-meaningful';
    }

    public function getDescription(): string
    {
        return 'Meaningful images should not have empty alt attributes.';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getDefaultSeverity(): string
    {
        return 'warning';
    }

    public function getWcagCriteria(): string
    {
        return '1.1.1 Non-text Content';
    }

    public function getFixHint(): string
    {
        return 'Provide descriptive alt text for meaningful images.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (isset($attrs['alt']) && trim($attrs['alt']) === '') {
                    $src = $attrs['src'] ?? '';
                    
                    if (preg_match('/(logo|icon|avatar|profile|hero|banner|chart|graph)/i', $src)) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            '<img> with meaningful src has an empty alt attribute.',
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
