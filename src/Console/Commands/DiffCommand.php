<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;
use MalikAd778\BladeAlly\Baseline\BaselineManager;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Engine\AnalysisOptions;
use MalikAd778\BladeAlly\Violations\ViolationDiff;
use MalikAd778\BladeAlly\Reporters\ReporterFactory;

class DiffCommand extends Command
{
    protected $signature = 'ally:diff {--path=} {--format=} {--ci}';
    protected $description = 'Show only new violations since the last baseline';

    public function handle(): int
    {
        $baselinePath = config('blade-ally.baseline', base_path('blade-ally-baseline.json'));

        if (!file_exists($baselinePath)) {
            $this->warn('No baseline file found. Run ally:baseline first.');
            return 0;
        }

        $this->info('Comparing current violations against baseline...');

        $analyzer = app(Analyzer::class);
        $options  = new AnalysisOptions(
            paths: $this->option('path') ? explode(',', $this->option('path')) : []
        );

        $result  = $analyzer->analyze($options);
        $manager = new BaselineManager($baselinePath);
        $diff    = new ViolationDiff();
        $newViolations = $diff->diff($result->violations, $manager->load());

        $count = count($newViolations->all());

        if ($count === 0) {
            $this->info('No new violations since last baseline.');
            return 0;
        }

        $format   = $this->option('format') ?: config('blade-ally.reporting.default_format', 'terminal');
        $reporter = ReporterFactory::make($format);

        $this->line("Found {$count} new violation(s):");
        $reporter->report(new \MalikAd778\BladeAlly\Engine\AnalysisResult(
            violations:    $newViolations,
            filesAnalyzed: $result->filesAnalyzed,
            elapsedMs:     $result->elapsedMs,
            config:        $result->config
        ));

        if ($this->option('ci')) {
            return 1;
        }

        return 0;
    }
}
