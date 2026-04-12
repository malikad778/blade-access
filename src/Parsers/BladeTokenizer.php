<?php
namespace MalikAd778\BladeAlly\Parsers;
class BladeTokenizer
{
    public function tokenize(string $content): array
    {
        $tokens = [];
        $length = strlen($content);
        $position = 0;
        $line = 1;
        while ($position < $length) {
            $char = $content[$position];
            if ($char === '<') {
                $inQuotes = false;
                $quoteChar = null;
                $endTag = $position;
                while ($endTag < $length) {
                    $c = $content[$endTag];
                    if ($c === '"' || $c === "'") {
                        if ($inQuotes && $c === $quoteChar) {
                            $inQuotes = false;
                            $quoteChar = null;
                        } elseif (!$inQuotes) {
                            $inQuotes = true;
                            $quoteChar = $c;
                        }
                    } elseif ($c === '>' && !$inQuotes) {
                        break;
                    }
                    $endTag++;
                }

                if ($endTag < $length && $content[$endTag] === '>') {
                    $tagContent = substr($content, $position, $endTag - $position + 1);
                    $tokens[] = ['type' => 'html_tag', 'content' => $tagContent, 'line' => $line];
                    $position = $endTag + 1;
                    $line += substr_count($tagContent, "\n");
                    continue;
                }
            } elseif ($char === '@') {
                $substr = substr($content, $position);
                if (preg_match('/^@([a-zA-Z0-9_]+)/', $substr, $m)) {
                    $parsedLen = strlen($m[0]);
                    
                    if (isset($substr[$parsedLen]) && $substr[$parsedLen] === '(') {
                        $parens = 0;
                        for ($i = $parsedLen; $i < strlen($substr); $i++) {
                            if ($substr[$i] === '(') $parens++;
                            if ($substr[$i] === ')') $parens--;
                            if ($parens === 0) {
                                $parsedLen = $i + 1;
                                break;
                            }
                        }
                    }
                    
                    $dirContent = substr($substr, 0, $parsedLen);
                    $tokens[] = ['type' => 'blade_directive', 'content' => $dirContent, 'line' => $line];
                    $position += $parsedLen;
                    $line += substr_count($dirContent, "\n");
                    continue;
                }
            }
            $nextTag = strpos($content, '<', $position + 1);
            $nextDir = strpos($content, '@', $position + 1);
            $nextPos = $length;
            if ($nextTag !== false && $nextTag < $nextPos) { $nextPos = $nextTag; }
            if ($nextDir !== false && $nextDir < $nextPos) { $nextPos = $nextDir; }
            $textContent = substr($content, $position, $nextPos - $position);
            if (trim($textContent) !== '') {
                $tokens[] = ['type' => 'text', 'content' => $textContent, 'line' => $line];
            }
            $position = $nextPos;
            $line += substr_count($textContent, "\n");
        }
        return $tokens;
    }
}
