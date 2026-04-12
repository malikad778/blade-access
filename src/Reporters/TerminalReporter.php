<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class TerminalReporter implements ReporterInterface
{
    public function report(AnalysisResult $result): void
    {
        $summary = $result->summary();
        echo "Blade Ally Analysis Complete\n";
        echo "Files analyzed : {$summary->filesAnalyzed}\n";
        echo "Total violations: {$summary->totalViolations} ({$summary->errors} errors, {$summary->warnings} warnings, {$summary->infos} info)\n";
        echo "Duration        : {$summary->elapsedMs}ms\n\n";

        foreach ($result->violations->groupByFile() as $file => $violations) {
            echo "{$file}\n";
            foreach ($violations as $v) {
                $hint = $v->fixHint ? " — {$v->fixHint}" : '';
                echo "  [{$v->severity}] line {$v->line}: {$v->ruleId}\n";
                echo "    {$v->message}{$hint}\n";
                if ($v->wcagUrl) {
                    echo "    {$v->wcagUrl}\n";
                }
            }
            echo "\n";
        }
    }

    public function reportToFile(AnalysisResult $result, string $outputFile): void
    {
        ob_start();
        $this->report($result);
        file_put_contents($outputFile, ob_get_clean());
    }
}
