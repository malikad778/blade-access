<?php

namespace MalikAd778\BladeAlly\Parsers;

use MalikAd778\BladeAlly\Parsers\Contracts\TemplateParserInterface;

class BladeTemplateParser implements TemplateParserInterface
{
    private BladeTokenizer $tokenizer;
    private HtmlAstBuilder $astBuilder;

    public function __construct(BladeTokenizer $tokenizer, HtmlAstBuilder $astBuilder)
    {
        $this->tokenizer = $tokenizer;
        $this->astBuilder = $astBuilder;
    }

    public function parse(string $content, string $filePath): array
    {
        $tokens = $this->tokenizer->tokenize($content);
        return $this->astBuilder->build($tokens);
    }
}
