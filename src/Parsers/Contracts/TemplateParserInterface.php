<?php

namespace MalikAd778\BladeAlly\Parsers\Contracts;

interface TemplateParserInterface
{
    public function parse(string $content, string $filePath): array;
}
