<?php

require_once __DIR__ . '/vendor/autoload.php';

use MalikAd778\BladeAlly\Parsers\BladeTokenizer;
use MalikAd778\BladeAlly\Parsers\HtmlAstBuilder;
use MalikAd778\BladeAlly\Rules\RuleRegistry;
use MalikAd778\BladeAlly\Engine\RuleContext;

$targetFile = $argv[1] ?? null;

if (!$targetFile || !file_exists($targetFile)) {
    echo "Usage: php analyze.php <path-to-html-or-blade-file>\n";
    exit(1);
}

echo "====================================================================\n";
echo "  Blade-Access / Laravel Blade Ally - WCAG 2.1 AA & BFSG Analyzer   \n";
echo "====================================================================\n";
echo "Analyzing target: $targetFile\n";
$content = file_get_contents($targetFile);
echo "File size: " . number_format(strlen($content)) . " bytes\n\n";

$startTime = microtime(true);

$tokenizer = new BladeTokenizer();
$tokens = $tokenizer->tokenize($content);
echo "Tokens parsed: " . count($tokens) . "\n";

$builder = new HtmlAstBuilder();
$ast = $builder->build($tokens);
echo "AST nodes generated: " . count($ast) . "\n";

$registry = new RuleRegistry();
$rules = $registry->getRules();
echo "Rules loaded: " . count($rules) . "\n\n";

$context = new RuleContext($targetFile, $ast, $content);
$violations = [];

foreach ($rules as $rule) {
    try {
        $found = $rule->check($ast, $context);
        foreach ($found as $v) {
            $violations[] = $v;
        }
    } catch (\Throwable $e) {
        echo "Error in rule " . get_class($rule) . ": " . $e->getMessage() . "\n";
    }
}

$elapsed = round((microtime(true) - $startTime) * 1000, 2);

echo "--------------------------------------------------------------------\n";
echo "Analysis completed in {$elapsed}ms. Total Violations Found: " . count($violations) . "\n";
echo "--------------------------------------------------------------------\n\n";

// Group by ruleId
$grouped = [];
foreach ($violations as $v) {
    $grouped[$v->ruleId][] = $v;
}

// Summary Table
echo sprintf("%-35s | %-12s | %-10s | %s\n", "Rule ID", "WCAG SC", "Severity", "Count");
echo str_repeat("-", 75) . "\n";

foreach ($grouped as $ruleId => $list) {
    $first = $list[0];
    echo sprintf(
        "%-35s | %-12s | %-10s | %d\n",
        $ruleId,
        $first->wcagCriteria ?: 'N/A',
        strtoupper($first->severity),
        count($list)
    );
}

echo "\n====================================================================\n";
echo "DETAILED VIOLATIONS BREAKDOWN\n";
echo "====================================================================\n";

$lines = explode("\n", $content);

foreach ($grouped as $ruleId => $list) {
    $first = $list[0];
    echo "\n>>> RULE: [{$ruleId}] (WCAG: {$first->wcagCriteria})\n";
    echo "Message: {$first->message}\n";
    if ($first->fixHint) {
        echo "Fix Hint: {$first->fixHint}\n";
    }
    echo "Occurrences: " . count($list) . "\n";
    
    // Show up to 5 sample occurrences with snippet
    $samples = array_slice($list, 0, 5);
    foreach ($samples as $idx => $v) {
        $lineNum = $v->line;
        $snippet = isset($lines[$lineNum - 1]) ? trim($lines[$lineNum - 1]) : '';
        if (strlen($snippet) > 120) {
            $snippet = substr($snippet, 0, 117) . '...';
        }
        echo "  #" . ($idx + 1) . " Line {$lineNum}: {$snippet}\n";
    }
}

// Save detailed report as JSON
$jsonReport = [
    'target' => $targetFile,
    'analyzed_at' => date('c'),
    'elapsed_ms' => $elapsed,
    'total_violations' => count($violations),
    'rule_breakdown' => array_map(function($list) {
        return [
            'count' => count($list),
            'severity' => $list[0]->severity,
            'wcag' => $list[0]->wcagCriteria,
            'message' => $list[0]->message,
            'fix_hint' => $list[0]->fixHint,
            'samples' => array_map(fn($v) => [
                'line' => $v->line,
                'column' => $v->column,
            ], array_slice($list, 0, 10))
        ];
    }, $grouped),
    'raw_violations' => array_map(fn($v) => $v->toArray(), $violations)
];

$jsonOutputFile = dirname($targetFile) . '/' . pathinfo($targetFile, PATHINFO_FILENAME) . '_blade_access_report.json';
file_put_contents($jsonOutputFile, json_encode($jsonReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nDetailed JSON report saved to: $jsonOutputFile\n";
