<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

class Tools
{
    private const DATE_PATTERN = 'Y-m-d-H-i-s';

    public function getScenarioName(): string
    {
        return 'randomScenar' . date(static::DATE_PATTERN);
    }

    public function getYear(string $code, int $period): int
    {
        return array_rand(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years']);
    }

    public function getMonth(string $code, int $period, int $year): int
    {
        $months = ScenarioEnv::SELECTOR[$code]['periods'][$period]['years'][$year];

        return $months[array_rand($months)];
    }
}
