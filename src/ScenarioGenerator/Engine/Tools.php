<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Fs\Scenario\Ship\Ship;

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

    /** @return int[] */
    public function getHours(int $month): array
    {
        if (true === in_array($month, ScenarioEnv::MID_MONTHS, true)) {
            return ['sunrise' => ScenarioEnv::MID_SUNRISE_HOUR, 'sunset' => ScenarioEnv::MID_SUNSET_HOUR];
        } elseif (true === in_array($month, ScenarioEnv::WINTER_MONTHS, true)) {
            return ['sunrise' => ScenarioEnv::WINTER_SUNRISE_HOUR, 'sunset' => ScenarioEnv::WINTER_SUNSET_HOUR];
        } elseif (true === in_array($month, ScenarioEnv::SUMMER_MONTHS, true)) {
            return ['sunrise' => ScenarioEnv::SUMMER_SUNRISE_HOUR, 'sunset' => ScenarioEnv::SUMMER_SUNSET_HOUR];
        } else {
            throw new \InvalidArgumentException("Invalid month: '$month'");
        }
    }

    public function getHour(): int
    {
        return \random_int(ScenarioEnv::DAY_24_HOURS_CLOCK_MIN, ScenarioEnv::DAY_24_HOURS_CLOCK_MAX);
    }

    public function getMinutes(): int
    {
        return \random_int(ScenarioEnv::MINUTES_MIN, ScenarioEnv::MINUTES_MAX);
    }

    public function getWindSpeed(): int
    {
        return \random_int(ScenarioEnv::WIND_SPEED_MIN, ScenarioEnv::WIND_SPEED_MAX);
    }

    public function getSeaState(): int
    {
        return \random_int(ScenarioEnv::SEA_STATE_MIN, ScenarioEnv::SEA_STATE_MAX);
    }

    public function getWindDirection(): int
    {
        return \random_int(ScenarioEnv::HEADING_DEGREE_MIN, ScenarioEnv::HEADING_DEGREE_MAX);
    }

    public function getRain(): int
    {
        return \random_int(ScenarioEnv::RAIN_OFF, ScenarioEnv::RAIN_ON);
    }

    public function getVisibility(): int
    {
        return \random_int(ScenarioEnv::VISIBILITY_MIN, ScenarioEnv::VISIBILITY_MAX);
    }

    public function getRadarCondition(): int
    {
        return \random_int(ScenarioEnv::RADAR_CONDITION_MIN, ScenarioEnv::RADAR_CONDITION_MAX);
    }

    public function getAirControl(): int
    {
        return array_rand(ScenarioEnv::AIR_CONTROL);
    }

    public function getBattleType(): int
    {
        return array_rand(ScenarioEnv::BATTLE_TYPE);
    }

    public function getRandomShipQty(): int
    {
        return \random_int(2, 8);
    }

    public function getBigShipCount(int $shipQuantity): int
    {
        switch ($shipQuantity) {
            case 2:
                return 2;
            case 3:
                $values = [1, 3];

                return $values[array_rand($values)]; // 1 OR 3
            case 4:
                return 1;
            case 5:
                $values = [1, 2];

                return $values[array_rand($values)]; // 1 OR 2
            case 6:
            case 7:
                $values = [2, 3];

                return $values[array_rand($values)]; // 2 OR 3
            case 8:
                return random_int(2, 4); // 2 to 4
            default:
                throw new \InvalidArgumentException("Unsupported ship qty: {$shipQuantity}");
        }
    }

    public function getRandomCrewQuality(): string
    {
        return Ship::CREW_QUALITY[array_rand(Ship::CREW_QUALITY)];
    }

    public function getRandomCrewFatigue(): string
    {
        return Ship::CREW_FATIGUE_LEVEL[array_rand(Ship::CREW_FATIGUE_LEVEL)];
    }

    public function getRandomCrewNightTraining(): string
    {
        return Ship::CREW_NIGHT_TRAINING[array_rand(Ship::CREW_NIGHT_TRAINING)];
    }

    public function getRandomRadarLevel(int $year, string $navy): string
    {
        $levels = ScenarioEnv::RADAR_LEVELS[$year][$navy];

        return $levels[array_rand($levels)];
    }

    public function getRandomHeading(): int
    {
        return CoordinatesCalculator::DIVISION_HEADING[array_rand(CoordinatesCalculator::DIVISION_HEADING)];
    }

    public function getRandomEnemyDistance(): int
    {
        return random_int(CoordinatesCalculator::ENNEMY_RANGE_MIN, CoordinatesCalculator::ENNEMY_RANGE_MAX);
    }
}
