<?php

namespace MalikAd778\BladeAlly\Reporters;

use MalikAd778\BladeAlly\Engine\AnalysisResult;
use MalikAd778\BladeAlly\Reporters\Contracts\ReporterInterface;

class SarifReporter implements ReporterInterface
{
    public function report(AnalysisResult $result): void
    {
        echo $this->build($result);
    }

    public function reportToFile(AnalysisResult $result, string $outputFile): void
    {
        file_put_contents($outputFile, $this->build($result));
    }

    private function build(AnalysisResult $result): string
    {
        $rules   = [];
        $results = [];

        foreach ($result->violations->all() as $v) {
            if (!isset($rules[$v->ruleId])) {
                $rules[$v->ruleId] = [
                    'id'               => $v->ruleId,
                    'shortDescription' => ['text' => $v->message],
                    'helpUri'          => $v->wcagUrl ?: '',
                ];
            }

            $results[] = [
                'ruleId'    => $v->ruleId,
                'level'     => $v->severity === 'error' ? 'error' : 'warning',
                'message'   => ['text' => $v->message],
                'locations' => [[
                    'physicalLocation' => [
                        'artifactLocation' => ['uri' => $v->filePath, 'uriBaseId' => '%SRCROOT%'],
                        'region'           => [
                            'startLine'   => max(1, $v->line),
                            'startColumn' => max(1, $v->column),
                        ],
                    ],
                ]],
            ];
        }

        $sarif = [
            '$schema' => 'https://raw.githubusercontent.com/oasis-tcs/sarif-spec/master/Schemata/sarif-schema-2.1.0.json',
            'version' => '2.1.0',
            'runs'    => [[
                'tool'    => [
                    'driver' => [
                        'name'            => 'laravel-blade-ally',
                        'informationUri'  => 'https://github.com/malikad778/laravel-blade-ally',
                        'rules'           => array_values($rules),
                    ],
                ],
                'results' => $results,
            ]],
        ];

        return json_encode($sarif, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
