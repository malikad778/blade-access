<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;
use MalikAd778\BladeAlly\Rules\RuleRegistry;

class ExplainCommand extends Command
{
    protected $signature = 'ally:explain {rule}';
    protected $description = 'Explain a specific accessibility rule in detail';

    public function handle(): int
    {
        $ruleId   = $this->argument('rule');
        $registry = app(RuleRegistry::class);
        $rules    = $registry->getRules();

        if (!isset($rules[$ruleId])) {
            $this->error("Rule '{$ruleId}' not found.");
            $this->line('Run <comment>php artisan ally:rules</comment> to see all available rules.');
            return 1;
        }

        $rule = $rules[$ruleId];

        $this->line('');
        $this->line('<fg=cyan;options=bold>' . $rule->getId() . '</>');
        $this->line(str_repeat('─', 60));
        $this->line('<comment>Description:</comment>  ' . $rule->getDescription());
        $this->line('<comment>Category:</comment>     ' . $rule->getCategory());
        $this->line('<comment>Severity:</comment>     ' . $rule->getDefaultSeverity());
        $this->line('<comment>WCAG:</comment>         ' . $rule->getWcagCriteria());
        $this->line('<comment>Reference:</comment>    ' . $rule->getWcagUrl());
        $this->line('<comment>Fix hint:</comment>     ' . $rule->getFixHint());
        $this->line('');

        return 0;
    }
}
