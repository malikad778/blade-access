<?php

namespace MalikAd778\BladeAlly\Reporters\Contracts;

use MalikAd778\BladeAlly\Engine\AnalysisResult;

interface ReporterInterface
{
    public function report(AnalysisResult $result): void;

    public function reportToFile(AnalysisResult $result, string $outputFile): void;
}
