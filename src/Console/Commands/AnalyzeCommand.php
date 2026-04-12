<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Engine\AnalysisOptions;
use MalikAd778\BladeAlly\Reporters\ReporterFactory;

class AnalyzeCommand extends Command
{
    protected $signature = 'ally:analyze {--path=} {--min-severity=} {--format=} {--output=} {--ci}';
    protected $description = 'Analyze Blade templates for accessibility violations';

    public function handle(): int
    {
        $this->info('Starting Blade Ally analysis...');

        $analyzer = app(Analyzer::class);

        $options = new AnalysisOptions(
            paths:       $this->option('path') ? explode(',', $this->option('path')) : [],
            minSeverity: $this->option('min-severity') ?: null
        );

        $result  = $analyzer->analyze($options);
        $format  = $this->option('format') ?: config('blade-ally.reporting.default_format', 'terminal');
        $output  = $this->option('output');
        $reporter = ReporterFactory::make($format);

        if ($output) {
            $reporter->reportToFile($result, $output);
            $this->info("Report written to {$output}");
        } else {
            $reporter->report($result);
        }

        if ($this->option('ci') && $result->hasFailed()) {
            $this->error('CI check failed: accessibility violations detected.');
            return 1;
        }

        return 0;
    }
}
