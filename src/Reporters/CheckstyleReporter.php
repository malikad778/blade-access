<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class CheckstyleReporter implements ReporterInterface
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
        $xml   = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<checkstyle version=\"8.0\">\n";
        $files = [];

        foreach ($result->violations->all() as $v) {
            $files[$v->filePath][] = $v;
        }

        foreach ($files as $file => $violations) {
            $xml .= '  <file name="' . htmlspecialchars($file, ENT_QUOTES) . "\">\n";
            foreach ($violations as $v) {
                $sev     = $v->severity === 'error' ? 'error' : 'warning';
                $message = htmlspecialchars($v->message, ENT_QUOTES);
                $xml .= "    <error line=\"{$v->line}\" column=\"{$v->column}\" severity=\"{$sev}\" message=\"{$message}\" source=\"{$v->ruleId}\"/>\n";
            }
            $xml .= "  </file>\n";
        }

        $xml .= "</checkstyle>\n";
        return $xml;
    }
}
