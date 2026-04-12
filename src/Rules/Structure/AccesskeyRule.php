<?php

namespace MalikAd778\BladeAlly\Rules\Structure;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class AccesskeyRule extends AbstractRule
{
    public function getId(): string
    {
        return 'accesskey';
    }

    public function getDescription(): string
    {
        return 'The accesskey attribute should not be used as it overrides screen reader shortcuts.';
    }

    public function getCategory(): string
    {
        return 'Structure';
    }

    public function getWcagCriteria(): string
    {
        return '2.1.1 Keyboard';
    }

    public function getFixHint(): string
    {
        return 'Remove the "accesskey" attribute.';
    }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];

        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);

                if (isset($attrs['accesskey'])) {
                    $violations[] = $this->makeViolation(
                        $context->filePath,
                        $node['line'] ?? 1,
                        $node['column'] ?? 1,
                        'The accesskey attribute conflicts with screen reader operation and should be avoided.',
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
