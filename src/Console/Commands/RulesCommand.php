<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;

class RulesCommand extends Command
{
    protected $signature = 'ally:rules {--category=} {--severity=}';
    protected $description = 'List all available accessibility rules';

    public function handle(): int
    {
        $registry = app(\MalikAd778\BladeAlly\Rules\RuleRegistry::class);
        $rules = $registry->all();
        
        $categoryFilter = $this->option('category');
        $severityFilter = $this->option('severity');
        
        $display = [];
        
        foreach ($rules as $id => $rule) {
            $cat = $rule->getCategory();
            $sev = $rule->getDefaultSeverity();
            
            if ($categoryFilter && strtolower($cat) !== strtolower($categoryFilter)) continue;
            if ($severityFilter && strtolower($sev) !== strtolower($severityFilter)) continue;
            
            $display[] = [
                $id,
                $cat,
                $sev,
                $rule->getDescription()
            ];
        }
        
        $this->table(['Rule ID', 'Category', 'Default Severity', 'Description'], $display);
        
        return 0;
    }
}
