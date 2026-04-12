<?php

namespace MalikAd778\BladeAlly\Reporters;

use Exception;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class ReporterFactory
{
    public static function make(string $format): ReporterInterface
    {
        return match ($format) {
            'json' => new JsonReporter(),
            'terminal' => new TerminalReporter(),
            'github' => new GithubAnnotationReporter(),
            'junit' => new JUnitReporter(),
            'sarif' => new SarifReporter(),
            'html' => new HtmlReporter(),
            'checkstyle' => new CheckstyleReporter(),
            default => throw new Exception("Unsupported reporter format: {$format}")
        };
    }
}
