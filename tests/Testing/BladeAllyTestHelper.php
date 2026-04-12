<?php

namespace MalikAd778\BladeAlly\Tests\Testing;

use Illuminate\Support\Facades\Facade;
use MalikAd778\BladeAlly\Engine\Analyzer;
use MalikAd778\BladeAlly\Engine\AnalysisOptions;

trait BladeAllyTestHelper
{
    public function assertAllyViolation(string $viewName, string $ruleId): void
    {
        $result  = $this->runAnalysis($viewName);
        $matched = false;
        foreach ($result->violations as $violation) {
            if ($violation->ruleId === $ruleId) {
                $matched = true;
                break;
            }
        }
        AssertViolation::assertTrue($matched, "Expected violation for rule [{$ruleId}] was not found in [{$viewName}].");
    }

    public function assertNoAllyViolations(string $viewName): void
    {
        $result = $this->runAnalysis($viewName);
        $count  = count($result->violations->all());
        AssertNoViolations::assertTrue($count === 0, "{$count} accessibility violation(s) found in [{$viewName}].");
    }

    public function assertNoAllyErrors(string $viewName): void
    {
        $result = $this->runAnalysis($viewName);
        $errors = $result->violations->countBySeverity('error');
        AssertNoViolations::assertTrue($errors === 0, "{$errors} accessibility error(s) found in [{$viewName}].");
    }

    public function fakeBladeAlly(): FakeAnalyzer
    {
        $fake = new FakeAnalyzer();
        $this->app->instance(Analyzer::class, $fake);
        return $fake;
    }

    private function runAnalysis(string $viewName): \MalikAd778\BladeAlly\Engine\AnalysisResult
    {
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        $analyzer = app(Analyzer::class);
        $options  = new AnalysisOptions(paths: [dirname($viewPath)]);

        $tmpDir  = sys_get_temp_dir() . '/blade-ally-test-' . uniqid();
        mkdir($tmpDir);
        $tmpFile = $tmpDir . '/' . basename($viewPath);
        copy($viewPath, $tmpFile);

        $result = $analyzer->analyze(new AnalysisOptions(paths: [$tmpDir]));

        unlink($tmpFile);
        rmdir($tmpDir);

        return $result;
    }
}
