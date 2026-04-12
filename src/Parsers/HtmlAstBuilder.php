<?php

namespace MalikAd778\BladeAlly\Parsers;

class HtmlAstBuilder
{
    public function build(array $tokens): array
    {
        $ast   = [];
        $stack = [];

        foreach ($tokens as $token) {
            if ($token['type'] === 'html_tag') {
                $content = trim($token['content'] ?? '');

                if (preg_match('/^<([a-zA-Z][a-zA-Z0-9\-]*)([^>]*?)(\/?)>$/s', $content, $m)) {
                    $tagName      = strtolower($m[1]);
                    $isSelfClose  = !empty($m[3]) || in_array($tagName, [
                        'area','base','br','col','embed','hr','img','input',
                        'link','meta','param','source','track','wbr',
                    ], true);

                    $node = [
                        'nodeType'   => 'Element',
                        'tagName'    => $tagName,
                        'attributes' => $this->parseAttributes($m[2]),
                        'line'       => $token['line'] ?? 1,
                        'column'     => 1,
                        'children'   => [],
                        'raw'        => $content,
                    ];

                    if ($isSelfClose) {
                        $this->appendNode($ast, $stack, $node);
                    } else {
                        $stack[] = $node;
                    }
                } elseif (preg_match('/^<\/([a-zA-Z][a-zA-Z0-9\-]*)>$/s', $content, $m)) {
                    $tagName = strtolower($m[1]);
                    $this->closeTag($ast, $stack, $tagName);
                }
            } elseif ($token['type'] === 'text') {
                $node = [
                    'nodeType' => 'Text',
                    'content'  => $token['content'],
                    'line'     => $token['line'] ?? 1,
                ];
                $this->appendNode($ast, $stack, $node);
            } elseif ($token['type'] === 'blade_directive') {
                $node = [
                    'nodeType' => 'BladeDirective',
                    'content'  => $token['content'],
                    'line'     => $token['line'] ?? 1,
                ];
                $this->appendNode($ast, $stack, $node);
            }
        }

        while (!empty($stack)) {
            $orphan = array_pop($stack);
            if (empty($stack)) {
                $ast[] = $orphan;
            } else {
                $stack[count($stack) - 1]['children'][] = $orphan;
            }
        }

        return $ast;
    }

    private function appendNode(array &$ast, array &$stack, array $node): void
    {
        if (empty($stack)) {
            $ast[] = $node;
        } else {
            $stack[count($stack) - 1]['children'][] = $node;
        }
    }

    private function closeTag(array &$ast, array &$stack, string $tagName): void
    {
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            if ($stack[$i]['tagName'] === $tagName) {
                $closed = array_splice($stack, $i);
                $root   = array_shift($closed);

                foreach (array_reverse($closed) as $orphan) {
                    $root['children'][] = $orphan;
                }

                if (empty($stack)) {
                    $ast[] = $root;
                } else {
                    $stack[count($stack) - 1]['children'][] = $root;
                }
                return;
            }
        }
    }

    protected function parseAttributes(string $attrStr): array
    {
        $attributes = [];
        preg_match_all(
            '/([a-zA-Z0-9\-_:.@]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/',
            $attrStr,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $name              = strtolower($match[1]);
            $value             = $match[2] ?? $match[3] ?? $match[4] ?? null;
            $attributes[$name] = $value;
        }
        return $attributes;
    }
}
