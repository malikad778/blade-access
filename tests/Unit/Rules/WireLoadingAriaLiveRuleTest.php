<?php

use MalikAd778\BladeAlly\Rules\Livewire\WireLoadingAriaLiveRule;
use MalikAd778\BladeAlly\Engine\RuleContext;
use MalikAd778\BladeAlly\Parsers\BladeTokenizer;
use MalikAd778\BladeAlly\Parsers\HtmlAstBuilder;

function analyzeWireLoadingAriaLive(string $html): array
{
    $tokenizer = new BladeTokenizer();
    $tokens = $tokenizer->tokenize($html);
    
    $builder = new HtmlAstBuilder();
    $ast = $builder->build($tokens);
    
    $rule = new WireLoadingAriaLiveRule();
    $context = new RuleContext('test.blade.php');
    
    return $rule->check($ast, $context);
}

it('passes when wire:loading element has aria-live="polite"', function () {
    $html = '<div wire:loading aria-live="polite">Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    expect($violations)->toBeEmpty();
});

it('passes when wire:loading element has aria-live="assertive"', function () {
    $html = '<div wire:loading aria-live="assertive">Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    expect($violations)->toBeEmpty();
});

it('passes when wire:loading element has role="status"', function () {
    $html = '<div wire:loading role="status">Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    expect($violations)->toBeEmpty();
});

it('passes when wire:loading element has role="alert"', function () {
    $html = '<div wire:loading role="alert">Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    expect($violations)->toBeEmpty();
});

it('fails when wire:loading element lacks aria-live and role', function () {
    $html = '<div wire:loading>Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    expect($violations)->toHaveCount(1);
    expect($violations[0]->ruleId)->toBe('wire-loading-aria-live');
    expect($violations[0]->message)->toBe('Element uses wire:loading but lacks aria-live="polite". Screen readers may ignore visual loading states.');
});

it('passes when wire:loading specifies a target because it applies differently', function () {
    $html = '<div wire:loading wire:target="submit">Loading...</div>';
    $violations = analyzeWireLoadingAriaLive($html);
    
    
    expect($violations)->toBeEmpty();
});
