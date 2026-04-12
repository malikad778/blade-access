<?php

namespace MalikAd778\BladeAlly;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use MalikAd778\BladeAlly\Caching\AnalysisCache;
use MalikAd778\BladeAlly\Caching\CacheDriver;
use MalikAd778\BladeAlly\Console\Commands\AnalyzeCommand;
use MalikAd778\BladeAlly\Console\Commands\BaselineCommand;
use MalikAd778\BladeAlly\Console\Commands\DiffCommand;
use MalikAd778\BladeAlly\Console\Commands\ExplainCommand;
use MalikAd778\BladeAlly\Console\Commands\InitCommand;
use MalikAd778\BladeAlly\Console\Commands\RulesCommand;
use MalikAd778\BladeAlly\Discovery\BladeFileDiscovery;
use MalikAd778\BladeAlly\Discovery\LivewireComponentDiscovery;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Ignores\IgnoreFileParser;
use MalikAd778\BladeAlly\Ignores\IgnoreManager;
use MalikAd778\BladeAlly\Parsers\BladeTemplateParser;
use MalikAd778\BladeAlly\Parsers\BladeTokenizer;
use MalikAd778\BladeAlly\Parsers\HtmlAstBuilder;
use MalikAd778\BladeAlly\Parsers\LivewireClassInspector;
use MalikAd778\BladeAlly\Parsers\LivewireComponentParser;
use MalikAd778\BladeAlly\Rules\RuleRegistry;

class BladeAllyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blade-ally.php', 'blade-ally');

        $this->app->singleton(BladeTokenizer::class,          fn () => new BladeTokenizer());
        $this->app->singleton(HtmlAstBuilder::class,          fn () => new HtmlAstBuilder());
        $this->app->singleton(BladeTemplateParser::class,     fn ($app) => new BladeTemplateParser($app->make(BladeTokenizer::class), $app->make(HtmlAstBuilder::class)));
        $this->app->singleton(LivewireClassInspector::class,  fn () => new LivewireClassInspector());
        $this->app->singleton(LivewireComponentParser::class, fn ($app) => new LivewireComponentParser($app->make(LivewireClassInspector::class)));
        $this->app->singleton(BladeFileDiscovery::class,      fn ($app) => new BladeFileDiscovery($app->make(Filesystem::class)));
        $this->app->singleton(LivewireComponentDiscovery::class, fn ($app) => new LivewireComponentDiscovery($app->make(Filesystem::class)));
        $this->app->singleton(IgnoreFileParser::class,        fn ($app) => new IgnoreFileParser($app->make(Filesystem::class)));
        $this->app->singleton(IgnoreManager::class,           fn () => new IgnoreManager());

        $this->app->singleton(CacheDriver::class, function ($app) {
            $cfg = config('blade-ally.cache', []);
            return new CacheDriver(
                $cfg['directory'] ?? storage_path('framework/cache/blade-ally'),
                $cfg['ttl'] ?? 3600,
                $app->make(Filesystem::class)
            );
        });

        $this->app->singleton(AnalysisCache::class, function ($app) {
            $enabled = config('blade-ally.cache.enabled', true);
            return new AnalysisCache($app->make(CacheDriver::class), $enabled);
        });

        $this->app->singleton(RuleRegistry::class, fn () => new RuleRegistry(config('blade-ally') ?: []));

        $this->app->singleton(Analyzer::class, fn ($app) => new Analyzer($app['config']['blade-ally'] ?? []));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/blade-ally.php' => config_path('blade-ally.php'),
            ], 'blade-ally-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/blade-ally'),
            ], 'blade-ally-views');

            $this->commands([
                AnalyzeCommand::class,
                BaselineCommand::class,
                DiffCommand::class,
                ExplainCommand::class,
                InitCommand::class,
                RulesCommand::class,
            ]);
        }
    }
}
