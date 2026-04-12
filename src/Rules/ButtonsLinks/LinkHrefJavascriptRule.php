<?php

namespace MalikAd778\BladeAlly\Rules\ButtonsLinks;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class LinkHrefJavascriptRule extends AbstractRule
{
    public function getId(): string
    {
        return 'link-href-javascript';
    }

    public function getDescription(): string
    {
        return 'Links must not use javascript: protocol in their href attribute.';
    }

    public function getCategory(): string
    {
        return 'ButtonsLinks';
    }

    public function getWcagCriteria(): string
    {
        return '2.1.1 Keyboard';
    }

    public function getFixHint(): string
    {
        return 'Use a <button> element instead of a link for javascript actions, or point href to a valid URL and preventDefault in JS.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'a') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                
                if (isset($attrs['href'])) {
                    $href = strtolower(trim($attrs['href']));
                    if (str_starts_with($href, 'javascript:')) {
                        $violations[] = $this->makeViolation(
                            $context->filePath,
                            $node['line'] ?? 1,
                            $node['column'] ?? 1,
                            '<a> element uses "javascript:" in its href attribute which can cause keyboard accessibility issues.',
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
