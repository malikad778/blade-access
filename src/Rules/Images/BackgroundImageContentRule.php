<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class BackgroundImageContentRule extends AbstractRule
{
    public function getId(): string
    {
        return 'background-image-content';
    }

    public function getDescription(): string
    {
        return 'CSS background images used as content should be <img> tags.';
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
        return 'Convert inline background-image to an <img> tag with alt text if it represents content.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $style = strtolower($attrs['style'] ?? '');
                $role = strtolower($attrs['role'] ?? '');
                
                if (str_contains($style, 'background-image:')) {
                    if ($role !== 'img' && !isset($attrs['aria-label'])) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Element uses a CSS background image without role="img" or aria-label.',
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
