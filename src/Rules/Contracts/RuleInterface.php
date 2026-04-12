<?php

namespace MalikAd778\BladeAlly\Rules\Contracts;

use MalikAd778\BladeAlly\Engine\RuleContext;

interface RuleInterface
{
    public function getId(): string;

    public function getDescription(): string;

    public function getCategory(): string;

    public function getDefaultSeverity(): string;

    public function getWcagCriteria(): string;

    public function getWcagUrl(): string;

    public function getFixHint(): string;

    public function check(array $ast, RuleContext $context): array;
}
