<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ImgAltRedundantRule extends AbstractRule
{
    public function getId(): string
    {
        return 'img-alt-redundant';
    }

    public function getDescription(): string
    {
        return 'Alt text should not be redundant with adjacent text strings.';
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
        return 'Remove words like "image of" or "picture of" from alt text.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $alt = strtolower($attrs['alt'] ?? '');
                
                if ($alt !== '') {
                    if (str_contains($alt, 'image of') || str_contains($alt, 'picture of') || str_contains($alt, 'photo of')) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            '<img> alt attribute contains redundant structural phrases (e.g. "image of").',
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
