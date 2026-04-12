<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class JsonReporter implements ReporterInterface
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
        $data = [
            'analyzed_files' => $result->filesAnalyzed,
            'duration_ms'    => $result->elapsedMs,
            'violations'     => array_map(fn ($v) => $v->toArray(), $result->violations->all()),
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
