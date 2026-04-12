<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;
use MalikAd778\BladeAlly\Baseline\BaselineManager;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Engine\AnalysisOptions;

class BaselineCommand extends Command
{
    protected $signature = 'ally:baseline {--path=}';
    protected $description = 'Create or update a baseline of current violations to suppress them in future runs';

    public function handle(): int
    {
        $this->info('Running analysis to capture current violations...');

        $analyzer = app(Analyzer::class);
        $options  = new AnalysisOptions(
            paths: $this->option('path') ? explode(',', $this->option('path')) : []
        );

        $result       = $analyzer->analyze($options);
        $baselinePath = config('blade-ally.baseline', base_path('blade-ally-baseline.json'));
        $manager      = new BaselineManager($baselinePath);
        $manager->save($result->violations);

        $count = count($result->violations->all());
        $this->info("Baseline saved to {$baselinePath} ({$count} violations suppressed).");

        return 0;
    }
}
