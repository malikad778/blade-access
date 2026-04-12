<?php

use MalikAd778\BladeAlly\Reporters\ReporterFactory;
use MalikAd778\BladeAlly\Reporters\JsonReporter;
use MalikAd778\BladeAlly\Reporters\TerminalReporter;
use MalikAd778\BladeAlly\Reporters\HtmlReporter;
use MalikAd778\BladeAlly\Reporters\SarifReporter;
use MalikAd778\BladeAlly\Reporters\CheckstyleReporter;

it('creates json reporter correctly', function () {
    $reporter = ReporterFactory::make('json');
    expect($reporter)->toBeInstanceOf(JsonReporter::class);
});

it('creates terminal reporter correctly', function () {
    $reporter = ReporterFactory::make('terminal');
    expect($reporter)->toBeInstanceOf(TerminalReporter::class);
});

it('creates html reporter correctly', function () {
    $reporter = ReporterFactory::make('html');
    expect($reporter)->toBeInstanceOf(HtmlReporter::class);
});

it('creates sarif reporter correctly', function () {
    $reporter = ReporterFactory::make('sarif');
    expect($reporter)->toBeInstanceOf(SarifReporter::class);
});

it('creates checkstyle reporter correctly', function () {
    $reporter = ReporterFactory::make('checkstyle');
    expect($reporter)->toBeInstanceOf(CheckstyleReporter::class);
});

it('throws exception for unsupported reporters', function () {
    expect(fn () => ReporterFactory::make('invalid_format'))
        ->toThrow(Exception::class, 'Unsupported reporter format: invalid_format');
});
