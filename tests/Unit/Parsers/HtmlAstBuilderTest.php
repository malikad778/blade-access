<?php

use MalikAd778\BladeAlly\Parsers\BladeTokenizer;
use MalikAd778\BladeAlly\Parsers\HtmlAstBuilder;

it('parses basic HTML elements', function () {
    $html = '<div><p>Hello World</p></div>';
    $tokenizer = new BladeTokenizer();
    $tokens = $tokenizer->tokenize($html);
    
    $builder = new HtmlAstBuilder();
    $ast = $builder->build($tokens);
    
    expect($ast)->toBeArray();
    expect($ast[0]['tagName'])->toBe('div');
    expect($ast[0]['children'][0]['tagName'])->toBe('p');
});

it('parses self-closing tags', function () {
    $html = '<div><br/><hr></div>';
    $tokenizer = new BladeTokenizer();
    $tokens = $tokenizer->tokenize($html);
    
    $builder = new HtmlAstBuilder();
    $ast = $builder->build($tokens);
    
    expect($ast[0]['children'][0]['tagName'])->toBe('br');
    expect($ast[0]['children'][1]['tagName'])->toBe('hr');
});

it('parses blade directives correctly', function () {
    $html = '<div @if($true) class="visible" @endif></div>';
    $tokenizer = new BladeTokenizer();
    $tokens = $tokenizer->tokenize($html);
    
    $builder = new HtmlAstBuilder();
    $ast = $builder->build($tokens);
    
    
    
    expect($ast)->toBeArray();
});
