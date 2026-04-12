<?php

namespace MalikAd778\BladeAlly\Parsers;

class LivewireComponentParser
{
    private LivewireClassInspector $inspector;

    public function __construct(LivewireClassInspector $inspector)
    {
        $this->inspector = $inspector;
    }

    public function parse(array $component): array
    {
        $classInfo = $this->inspector->inspect($component['class'] ?? '');
        return [
            'view'  => $component['view']  ?? '',
            'class' => $classInfo,
        ];
    }
}
