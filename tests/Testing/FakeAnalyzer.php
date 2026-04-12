<?php

namespace MalikAd778\BladeAlly\Tests\Testing;

use MalikAd778\BladeAlly\Engine\AnalysisOptions;
use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Violations\ViolationCollection;

class FakeAnalyzer extends Analyzer
{
    private array $analyzedPaths = [];

    public function __construct()
    {
        parent::__construct([]);
    }

    public function analyze(?AnalysisOptions $options = null): AnalysisResult
    {
        foreach ($options?->paths ?? [] as $path) {
            $this->analyzedPaths[] = $path;
        }

        return new AnalysisResult(
            violations:    new ViolationCollection(),
            filesAnalyzed: 0,
            elapsedMs:     0.0,
            config:        []
        );
    }

    public function assertAnalyzed(string $path): void
    {
        if (!in_array($path, $this->analyzedPaths, true)) {
            throw new \PHPUnit\Framework\AssertionFailedError("Path [{$path}] was not analyzed.");
        }
    }

    public function getAnalyzedPaths(): array
    {
        return $this->analyzedPaths;
    }
}
