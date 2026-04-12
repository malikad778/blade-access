<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class GithubAnnotationReporter implements ReporterInterface
{
    public function report(AnalysisResult $result): void
    {
        echo $this->build($result);
    }

    public function reportToFile(AnalysisResult $result, string $outputFile): void
    {
        file_put_contents($outputFile, $this->build($result));
    }

    private function build(AnalysisResult $result): string
    {
        $out = '';
        foreach ($result->violations->all() as $v) {
            $level   = $v->severity === 'error' ? 'error' : 'warning';
            $message = str_replace("\n", '%0A', $v->message);
            $out .= "::{$level} file={$v->filePath},line={$v->line},col={$v->column},title={$v->ruleId}::{$message}\n";
        }
        return $out;
    }
}
