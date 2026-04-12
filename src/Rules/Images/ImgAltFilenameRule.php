<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class ImgAltFilenameRule extends AbstractRule
{
    public function getId(): string
    {
        return 'img-alt-filename';
    }

    public function getDescription(): string
    {
        return 'Alt text should not be the image filename.';
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
        return 'Replace the filename with descriptive alt text.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'img') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $alt = $attrs['alt'] ?? '';
                
                if ($alt !== '' && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $alt)) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<img> alt attribute contains a filename or file extension.',
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
