<?php

use MalikAd778\BladeAlly\Parsers\BladeTokenizer;

it('tokenizes tags and content', function () {
    $tokenizer = new BladeTokenizer();
    
    $html = '<div><p>Simple text</p></div>';
    $tokens = $tokenizer->tokenize($html);
    
    expect($tokens)->toBeArray();
    
    expect($tokens[0]['type'] ?? null)->toBe('tag_open');
});

it('tokenizes blade block commands', function () {
    $tokenizer = new BladeTokenizer();
    
    $html = '@foreach($items as $item) <div>@if(true)<span>Hi</span>@endif</div> @endforeach';
    $tokens = $tokenizer->tokenize($html);
    
    
    expect($tokens)->toBeArray();
});
