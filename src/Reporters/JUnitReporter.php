<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class JUnitReporter implements ReporterInterface
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
        $violations = $result->violations->all();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<testsuites>\n";
        $xml .= "  <testsuite name=\"Blade Ally\" tests=\"{$result->filesAnalyzed}\" failures=\"" . count($violations) . "\">\n";

        foreach ($violations as $v) {
            $file    = htmlspecialchars($v->filePath, ENT_QUOTES);
            $message = htmlspecialchars($v->message,  ENT_QUOTES);
            $hint    = htmlspecialchars($v->fixHint,  ENT_QUOTES);
            $xml .= "    <testcase name=\"{$v->ruleId}\" file=\"{$file}\" line=\"{$v->line}\">\n";
            $xml .= "      <failure message=\"{$message}\" type=\"{$v->severity}\"><![CDATA[{$hint}]]></failure>\n";
            $xml .= "    </testcase>\n";
        }

        $xml .= "  </testsuite>\n</testsuites>\n";
        return $xml;
    }
}
