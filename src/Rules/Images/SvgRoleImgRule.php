<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class SvgRoleImgRule extends AbstractRule
{
    public function getId(): string
    {
        return 'svg-role-img';
    }

    public function getDescription(): string
    {
        return 'SVGs used as images should have role="img".';
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
        return 'Add role="img" to <svg> tags that represent meaningful images.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'svg') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $isHidden = ($attrs['aria-hidden'] ?? 'false') === 'true';
                $role = $attrs['role'] ?? '';
                $hasLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']);

                if (!$isHidden && $hasLabel && $role !== 'img') {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        '<svg> has an accessible label but is missing role="img".',
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
