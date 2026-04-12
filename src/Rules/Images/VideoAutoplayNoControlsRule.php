<?php

namespace MalikAd778\BladeAlly\Rules\Images;

use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Rules\AbstractRule;

class VideoAutoplayNoControlsRule extends AbstractRule
{
    public function getId(): string { return 'video-autoplay-no-controls'; }
    public function getDescription(): string { return '<video autoplay> without controls or muted can cause audio to play unexpectedly.'; }
    public function getCategory(): string { return 'Images'; }
    public function getDefaultSeverity(): string { return 'error'; }
    public function getWcagCriteria(): string { return '1.4.2 Audio Control'; }
    public function getFixHint(): string { return 'Add the controls attribute or muted to autoplay videos.'; }

    public function check(array $ast, RuleContext $context): array
    {
        $violations = [];
        foreach ($ast as $node) {
            if (($node['nodeType'] ?? '') === 'Element' && strtolower($node['tagName'] ?? '') === 'video') {
                $attrs = array_change_key_case($node['attributes'] ?? [], CASE_LOWER);
                if (isset($attrs['autoplay']) && !isset($attrs['controls']) && !isset($attrs['muted'])) {
                    $violations[] = $this->makeViolation($context->filePath, $node['line'] ?? 1, $node['column'] ?? 1,
                        '<video autoplay> without controls or muted may play audio unexpectedly.');
                }
            }
            if (!empty($node['children'])) {
                $violations = array_merge($violations, $this->check($node['children'], $context));
            }
        }
        return $violations;
    }

}
