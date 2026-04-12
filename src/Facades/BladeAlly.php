<?php

namespace MalikAd778\BladeAlly\Facades;

use Illuminate\Support\Facades\Facade;
use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Engine\Analyzer;


class BladeAlly extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Analyzer::class;
    }
}
