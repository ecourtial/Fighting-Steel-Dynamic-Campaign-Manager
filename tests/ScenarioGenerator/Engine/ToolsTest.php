<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\ScenarioGenerator\Engine\ScenarioEnv;
use App\ScenarioGenerator\Engine\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    private static Tools $tools;

    public static function setUpBeforeClass(): void
    {
        static::$tools = new Tools();
    }

    public function testGetScenarioName(): void
    {
        $regex = '/^randomScenar2[0-9]{3}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}$/';
        $scenarioName = static::$tools->getScenarioName();

        static::assertEquals(1, preg_match($regex, $scenarioName));
    }

    public function testGetYear(): void
    {
        $code = 'Atlantic';
        $period = 3;
        $year = static::$tools->getYear($code, $period);

        $keys = array_keys(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years']);

        static::assertTrue(in_array($year, $keys, true));
    }

    public function testGetMonth(): void
    {
        $code = 'Atlantic';
        $period = 3;
        $year = 1943;
        $month = static::$tools->getMonth($code, $period, $year);

        $values = ScenarioEnv::SELECTOR[$code]['periods'][$period]['years'][$year];

        static::assertTrue(in_array($month, $values, true));
    }
}
