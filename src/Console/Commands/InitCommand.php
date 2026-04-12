<?php

namespace MalikAd778\BladeAlly\Console\Commands;

use Illuminate\Console\Command;

class InitCommand extends Command
{
    protected $signature = 'ally:init';
    protected $description = 'Initialize Blade Ally configuration and empty baseline';

    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'blade-ally-config']);

        $baselinePath = base_path('blade-ally-baseline.json');
        
        if (!file_exists($baselinePath)) {
            $stubPath = __DIR__ . '/../../../stubs/baseline.stub';
            if (file_exists($stubPath)) {
                $stub = file_get_contents($stubPath);
                $stub = str_replace(
                    ['{{ datetime }}', '{{ version }}'], 
                    [date('c'), '1.0.0'], 
                    $stub
                );
                file_put_contents($baselinePath, $stub);
            } else {
                file_put_contents($baselinePath, json_encode(['generated_at' => date('c'), 'version' => '1.0.0', 'violations' => []], JSON_PRETTY_PRINT));
            }
            $this->info('Created empty baseline file at ' . $baselinePath);
        }

        $this->info('Blade Ally initialized successfully.');
        
        return 0;
    }
}
