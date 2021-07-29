<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\Core\Fs\Scenario\Ship\Ship;
use App\ScenarioGenerator\Engine\CoordinatesCalculator;
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

    public function testGetHour(): void
    {
        $hour = static::$tools->getHour();
        static::assertTrue($hour >= 0 && $hour <= 23);
    }

    public function testGetHours(): void
    {
        $hours = static::$tools->getHours(5);
        $startHour = $hours['sunrise'];
        $endHour = $hours['sunset'];
        static::assertEquals($startHour, 6);
        static::assertEquals($endHour, 19);

        $hours = static::$tools->getHours(1);
        $startHour = $hours['sunrise'];
        $endHour = $hours['sunset'];
        static::assertEquals($startHour, 7);
        static::assertEquals($endHour, 17);

        $hours = static::$tools->getHours(7);
        $startHour = $hours['sunrise'];
        $endHour = $hours['sunset'];
        static::assertEquals($startHour, 5);
        static::assertEquals($endHour, 21);

        static::expectExceptionMessage("Invalid month: '13'");
        static::$tools->getHours(13);
    }

    public function testGetMinutes(): void
    {
        $minute = static::$tools->getMinutes();
        static::assertTrue($minute >= 1 && $minute <= 59);
    }

    public function testGetWindSpeed(): void
    {
        $value = static::$tools->getWindSpeed();
        static::assertTrue($value >= 1 && $value <= 40);
    }

    public function testGetSeaState(): void
    {
        $value = static::$tools->getSeaState();
        static::assertTrue($value >= 1 && $value <= 8);
    }

    public function testGetWindDirection(): void
    {
        $value = static::$tools->getWindDirection();
        static::assertTrue($value >= 1 && $value <= 360);
    }

    public function testGetRain(): void
    {
        $value = static::$tools->getRain();
        static::assertTrue(0 === $value || 1 === $value);
    }

    public function testGetVisibility(): void
    {
        $value = static::$tools->getVisibility();
        static::assertTrue($value >= 40 && $value <= 100);
    }

    public function testGetRadarCondition(): void
    {
        $value = static::$tools->getRadarCondition();
        static::assertTrue($value >= 0 && $value <= 100);
    }

    public function testGetAirControl(): void
    {
        $value = static::$tools->getAirControl();
        static::assertTrue(in_array($value, [2, 3, 4, 5], true));
    }

    public function testGetBattleType(): void
    {
        $value = static::$tools->getBattleType();
        static::assertTrue(in_array($value, [0, 3], true));
    }

    public function testGetRandomShipQty(): void
    {
        $value = static::$tools->getRandomShipQty();
        static::assertTrue($value >= 2 && $value <= 8);
    }

    public function testBigShipCount(): void
    {
        static::assertEquals(2, static::$tools->getBigShipCount(2));
        static::assertEquals(1, static::$tools->getBigShipCount(4));

        $value = static::$tools->getBigShipCount(3);
        static::assertTrue(in_array($value, [1, 3], true));

        $value = static::$tools->getBigShipCount(5);
        static::assertTrue(in_array($value, [1, 2], true));

        $value = static::$tools->getBigShipCount(6);
        static::assertTrue(in_array($value, [2, 3], true));

        $value = static::$tools->getBigShipCount(7);
        static::assertTrue(in_array($value, [2, 3], true));

        $value = static::$tools->getBigShipCount(8);
        static::assertTrue(in_array($value, [2, 3, 4], true));

        static::expectExceptionMessage('Unsupported ship qty: 9');
        static::$tools->getBigShipCount(9);
    }

    public function testGetRandomCrewQuality(): void
    {
        $value = static::$tools->getRandomCrewQuality();
        static::assertTrue(in_array($value, Ship::CREW_QUALITY, true));
    }

    public function testGetRandomCrewFatigue(): void
    {
        $value = static::$tools->getRandomCrewFatigue();
        static::assertTrue(in_array($value, Ship::CREW_FATIGUE_LEVEL, true));
    }

    public function testGetRandomCrewNightTraining(): void
    {
        $value = static::$tools->getRandomCrewNightTraining();
        static::assertTrue(in_array($value, Ship::CREW_NIGHT_TRAINING, true));
    }

    public function testGetRandomRadarLevel(): void
    {
        $value = static::$tools->getRandomRadarLevel(1939, 'MN');
        static::assertTrue(in_array($value, ScenarioEnv::RADAR_LEVELS[1939]['MN'], true));
    }

    public function testGetRandomHeading(): void
    {
        $value = static::$tools->getRandomHeading();
        static::assertTrue(in_array($value, CoordinatesCalculator::DIVISION_HEADING, true));
    }

    public function testGetRandomEnemyDistance(): void
    {
        $value = static::$tools->getRandomEnemyDistance();
        static::assertTrue(
            $value >= CoordinatesCalculator::ENNEMY_RANGE_MIN
            && $value <= CoordinatesCalculator::ENNEMY_RANGE_MAX
        );
    }
}
