<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LinkOpensNewTabNoWarningRule extends AbstractRule
{
    public function getId(): string
    {
        return 'link-opens-new-tab-no-warning';
    }

    public function getDescription(): string
    {
        return 'Links opening in new tabs should visually/programmatically warn the user.';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getWcagCriteria(): string
    {
        return '3.2.2 On Input';
    }

    public function getFixHint(): string
    {
        return 'Add "(opens in a new tab)" screen-reader text or aria-label describing the behavior.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        $html = file_get_contents($context->filePath);

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                $target = strtolower($attrs['target'] ?? '');

                if ($target === '_blank') {
                    $hasAriaLabel = isset($attrs['aria-label']) || isset($attrs['aria-labelledby']) || isset($attrs['title']);
                    if (!$hasAriaLabel) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            'Link opens in new window/tab but lacks warning text or aria-label.',
                            'warning',
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
