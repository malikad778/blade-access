<?php

namespace MalikAd778\BladeAlly\Engine;

use MalikAd778\BladeAlly\Baseline\BaselineManager;
use MalikAd778\BladeAlly\Caching\AnalysisCache;
use MalikAd778\BladeAlly\Discovery\BladeFileDiscovery;
use MalikAd778\BladeAlly\Discovery\LivewireComponentDiscovery;
use MalikAd778\BladeAlly\Ignores\IgnoreManager;
use MalikAd778\BladeAlly\Parsers\BladeTemplateParser;
use MalikAd778\BladeAlly\Rules\RuleRegistry;
use MalikAd778\BladeAlly\Violations\ViolationCollection;
use MalikAd778\BladeAlly\Violations\ViolationDiff;

class Analyzer
{
    private RuleRegistry $ruleRegistry;
    private BladeFileDiscovery $bladeDiscovery;
    private LivewireComponentDiscovery $livewireDiscovery;
    private BladeTemplateParser $bladeParser;
    private AnalysisCache $cache;
    private IgnoreManager $ignoreManager;

    public function __construct(private readonly array $config)
    {
        $this->ruleRegistry      = app(RuleRegistry::class);
        $this->bladeDiscovery    = app(BladeFileDiscovery::class);
        $this->livewireDiscovery = app(LivewireComponentDiscovery::class);
        $this->bladeParser       = app(BladeTemplateParser::class);
        $this->cache             = app(AnalysisCache::class);
        $this->ignoreManager     = app(IgnoreManager::class);
    }

    public function analyze(?AnalysisOptions $options = null): AnalysisResult
    {
        $startTime     = microtime(true);
        $violations    = new ViolationCollection();
        $filesAnalyzed = 0;

        $paths       = $options?->paths    ?? $this->config['paths'] ?? [];
        $minSeverity = $options?->minSeverity ?? null;

        $ignoreFilePath = base_path('.blade-ally-ignore');
        if (file_exists($ignoreFilePath)) {
            $this->ignoreManager->loadFileIgnores(
                $ignoreFilePath,
                app(\MalikAd778\BladeAlly\Ignores\IgnoreFileParser::class)
            );
        }

        $bladeFiles = $this->bladeDiscovery->discover($paths);

        foreach ($bladeFiles as $file) {
            $this->ignoreManager->loadInlineIgnores($file, file_get_contents($file));

            $fileViolations = $this->analyzeBladeFile($file);
            foreach ($fileViolations as $violation) {
                if ($this->ignoreManager->isIgnored($violation)) {
                    continue;
                }
                if ($minSeverity && !$this->meetsMinSeverity($violation->severity, $minSeverity)) {
                    continue;
                }
                $violations->add($violation);
            }
            $filesAnalyzed++;
        }

        if ($this->config['livewire']['enabled'] ?? true) {
            $livewirePaths = $this->config['livewire']['paths'] ?? [];
            $components    = $this->livewireDiscovery->discover($livewirePaths);

            foreach ($components as $component) {
                $fileViolations = $this->analyzeLivewireComponent($component);
                foreach ($fileViolations as $violation) {
                    if ($this->ignoreManager->isIgnored($violation)) {
                        continue;
                    }
                    if ($minSeverity && !$this->meetsMinSeverity($violation->severity, $minSeverity)) {
                        continue;
                    }
                    $violations->add($violation);
                }
                $filesAnalyzed++;
            }
        }

        $baselinePath = $this->config['baseline'] ?? null;
        if ($baselinePath && file_exists($baselinePath)) {
            $baselineManager = new BaselineManager($baselinePath);
            $baseline        = $baselineManager->load();
            $diff            = new ViolationDiff();
            $violations      = $diff->diff($violations, $baseline);
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        return new AnalysisResult(
            violations:    $violations,
            filesAnalyzed: $filesAnalyzed,
            elapsedMs:     $elapsed,
            config:        $this->config
        );
    }

    private function analyzeBladeFile(string $file): array
    {
        $hash   = md5_file($file);
        $cached = $this->cache->get($file, $hash);
        if ($cached !== null) {
            return $cached;
        }

        $content    = file_get_contents($file);
        $ast        = $this->bladeParser->parse($content, $file);
        $context    = new RuleContext($file, $ast, $content, $this->config);
        $violations = [];

        foreach ($this->ruleRegistry->getRules() as $rule) {
            foreach ($rule->check($ast, $context) as $v) {
                $violations[] = $v;
            }
        }

        $this->cache->put($file, $hash, $violations);

        return $violations;
    }

    private function analyzeLivewireComponent(array $component): array
    {
        $viewPath = $component['view'] ?? null;
        if (!$viewPath || !file_exists($viewPath)) {
            return [];
        }

        $content    = file_get_contents($viewPath);
        $ast        = $this->bladeParser->parse($content, $viewPath);
        $context    = new RuleContext($viewPath, $ast, $content, $this->config, $component);
        $violations = [];

        foreach ($this->ruleRegistry->getRules() as $rule) {
            foreach ($rule->check($ast, $context) as $v) {
                $violations[] = $v;
            }
        }

        return $violations;
    }

    private function meetsMinSeverity(string $severity, string $minSeverity): bool
    {
        $levels = ['info' => 0, 'warning' => 1, 'error' => 2];
        return ($levels[$severity] ?? 0) >= ($levels[$minSeverity] ?? 0);
    }

    public function getRuleRegistry(): RuleRegistry
    {
        return $this->ruleRegistry;
    }
}
