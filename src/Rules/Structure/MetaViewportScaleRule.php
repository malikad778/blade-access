<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class MetaViewportScaleRule extends AbstractRule
{
    public function getId(): string
    {
        return 'meta-viewport-scale';
    }

    public function getDescription(): string
    {
        return 'Ensure user-scalable is not disabled and maximum-scale is not restricted to less than 2.0.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '1.4.4 Resize text';
    }

    public function getFixHint(): string
    {
        return 'Remove user-scalable=no, and ensure maximum-scale is 2.0 or higher (or remove it entirely).';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'meta') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $name = strtolower($attrs['name'] ?? '');

                if ($name === 'viewport') {
                    $content = strtolower($attrs['content'] ?? '');

                    if (str_contains($content, 'user-scalable=no') || str_contains($content, 'user-scalable=0')) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            '<meta name="viewport"> uses "user-scalable=no" preventing zoom.',
                            $this->getDefaultSeverity(),
                            $this->getFixHint()
                        );
                    }

                    if (preg_match('/maximum-scale=([0-9\.]+)/', $content, $matches)) {
                        $scale = (float)$matches[1];
                        if ($scale < 2.0) {
                            $violations[] = $this->makeViolation(
                                $context->filePath,
                                $node['line'] ?? 1,
                                $node['column'] ?? 1,
                                '<meta name="viewport"> restricts zooming to less than 2x scale.',
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
