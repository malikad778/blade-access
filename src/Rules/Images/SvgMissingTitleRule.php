<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class SvgMissingTitleRule extends AbstractRule
{
    public function getId(): string
    {
        return 'svg-missing-title';
    }

    public function getDescription(): string
    {
        return 'SVG acting as an image needs an accessible name.';
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
        return 'Add a <title> element inside the SVG or aria-label attribute. Decorational SVGs should have aria-hidden="true".';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'svg') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                $isHidden = ($attrs['aria-hidden'] ?? 'false') === 'true';
                $hasLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);
                $hasTitle = false;

                if (!empty($node['children'])) {
                    foreach ($node['children'] as $child) {
                        if (($child['nodeType'] ?? '') === 'Element' && strtolower($child['tagName'] ?? '') === 'title') {
                            $hasTitle = true;
                            break;
                        }
                    }
                }

                if (!$isHidden && !$hasLabel && !$hasTitle) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<svg> used without an accessible name (<title> or aria-label) and without aria-hidden="true".',
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
