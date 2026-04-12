<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ImgMissingAltRule extends AbstractRule
{
    public function getId(): string
    {
        return 'img-missing-alt';
    }

    public function getDescription(): string
    {
        return 'Images must have an alt attribute.';
    }

    public function getCategory(): string
    {
        return 'Images';
    }

    public function getDefaultSeverity(): string
    {
        return 'error';
    }

    public function getWcagCriteria(): string
    {
        return '1.1.1 Non-text Content';
    }

    public function getFixHint(): string
    {
        return 'Add alt="description" or alt="" if decorative.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
                $attrs = $node['attributes'] ?? [];
                
                $hasAlt = false;
                foreach (array_keys($attrs) as $key) {
                    if (strtolower($key) === 'alt') {
                        $hasAlt = true;
                        break;
                    }
                }

                if (!$hasAlt) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<img> tag is missing alt attribute.',
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
