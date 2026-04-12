<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class HtmlReporter implements ReporterInterface
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
        $summary    = $result->summary();
        $violations = $result->violations->groupByFile();
        $rows       = '';

        foreach ($violations as $file => $items) {
            foreach ($items as $v) {
                $sev  = htmlspecialchars($v->severity,  ENT_QUOTES);
                $f    = htmlspecialchars($v->filePath,  ENT_QUOTES);
                $msg  = htmlspecialchars($v->message,   ENT_QUOTES);
                $hint = htmlspecialchars($v->fixHint,   ENT_QUOTES);
                $rule = htmlspecialchars($v->ruleId,    ENT_QUOTES);
                $rows .= "<tr class=\"sev-{$sev}\"><td>{$f}:{$v->line}</td><td>{$rule}</td><td>{$sev}</td><td>{$msg}</td><td>{$hint}</td></tr>\n";
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Blade Ally Accessibility Report</title>
<style>
body{font-family:sans-serif;margin:2rem}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ddd;padding:.5rem;text-align:left;font-size:.875rem}
th{background:#f4f4f4}
.sev-error td:nth-child(3){color:#c0392b;font-weight:bold}
.sev-warning td:nth-child(3){color:#e67e22}
.sev-info td:nth-child(3){color:#2980b9}
</style>
</head>
<body>
<h1>Blade Ally Accessibility Report</h1>
<p>Files analyzed: <strong>{$summary->filesAnalyzed}</strong> &nbsp;|&nbsp;
   Errors: <strong>{$summary->errors}</strong> &nbsp;|&nbsp;
   Warnings: <strong>{$summary->warnings}</strong> &nbsp;|&nbsp;
   Info: <strong>{$summary->infos}</strong> &nbsp;|&nbsp;
   Duration: {$summary->elapsedMs}ms</p>
<table>
<thead><tr><th>Location</th><th>Rule</th><th>Severity</th><th>Message</th><th>Fix hint</th></tr></thead>
<tbody>{$rows}</tbody>
</table>
</body>
</html>
HTML;
    }
}
