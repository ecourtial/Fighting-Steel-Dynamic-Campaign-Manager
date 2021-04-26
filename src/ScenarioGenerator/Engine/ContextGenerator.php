<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

class ContextGenerator
{
    public function getHeaderData(int $month, int $year, string $scenarioName): string
    {
        $hour = $this->getHour();
        $minutes = $this->getMinutes();
        $monthName = ScenarioEnv::MONTHS[$month];
        $airSuperiority = $this->getAirControl();
        $visibility = $this->getVisibility();
        $radarCondition = $this->getRadarCondition();
        $windSpeed = $this->getWindSpeed();
        $windDirection = $this->getWindDirection();
        $seaState = $this->getSeaState();
        $rain = $this->getRain();
        $battletype = $this->getBattleType();

        $hours = $this->getHours($month);
        $sunriseHour = $hours['sunrise'];
        $sunsetHour = $hours['sunset'];

        return  <<<EOT
        [VERSION]
        VERSIONNUMBER=64
        [SCENARIODATA]
        TITLE=$scenarioName
        DESCRIPTION_LINECNT=1
        DESCRIPTION_LINE0=None
        START_TIME=$hour:$minutes
        START_TIME_HOUR=$hour
        START_TIME_MINUTE=$minutes
        SUNRISE=0$sunriseHour:30
        SUNSET=$sunsetHour:30
        SUNRISE_HOUR=$sunriseHour
        SUNRISE_MINUTE=30
        SUNSET_HOUR=$sunsetHour
        SUNSET_MINUTE=30
        MONTH=$monthName
        YEAR=$year
        BLUEBRIEFING_LINECNT=1
        BLUEBRIEFING_LINE0=Unavailable
        REDBRIEFING_LINECNT=1
        REDBRIEFING_LINE0=Unavailable
        V.CONDITION_BLUE=None
        V.CONDITION_RED=None
        AIR_SUPERIORITY=$airSuperiority
        LOCATION=North Atlantic
        VISIBILITY=$visibility
        RADARCONDITIONS=$radarCondition
        WIND_SPEED=$windSpeed
        WIND_DIRECTION=$windDirection
        VP_MODS=None
        MOONPHASE=None
        SEASTATE=$seaState
        GAMELENGTH=3:00
        GAMELENGTH_HOUR=3
        GAMELENGTH_MINUTE=00
        BLUE_NAVY=RN
        RED_NAVY=KM
        RAIN=$rain
        DDTRANSPORTS=0
        BLUE_TARGETBOX_X=0
        BLUE_TARGETBOX_Z=0
        BLUE_TARGETBOXDIM=9260
        RED_TARGETBOX_X=0
        RED_TARGETBOX_Z=0
        RED_TARGETBOX_DIM=0
        BLUE_DISENGAGE_HEADING=180
        RED_DISENGAGE_HEADING=180
        BATTLETYPE=$battletype
        BLUE_VICTORY_MODS=1.00
        RED_VICTORY_MODS=1.00
        BLUE_FORCE_TYPE=0
        RED_FORCE_TYPE=1
        MOVIE=0
        [SCENARIOINFO]
        EOT;
    }

    /** @return int[] */
    private function getHours(int $month): array
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

    private function getHour(): int
    {
        return \random_int(ScenarioEnv::DAY_24_HOURS_CLOCK_MIN, ScenarioEnv::DAY_24_HOURS_CLOCK_MAX);
    }

    private function getMinutes(): int
    {
        return \random_int(ScenarioEnv::MINUTES_MIN, ScenarioEnv::MINUTES_MAX);
    }

    private function getWindSpeed(): int
    {
        return \random_int(ScenarioEnv::WIND_SPEED_MIN, ScenarioEnv::WIND_SPEED_MAX);
    }

    private function getSeaState(): int
    {
        return \random_int(ScenarioEnv::SEA_STATE_MIN, ScenarioEnv::SEA_STATE_MAX);
    }

    private function getWindDirection(): int
    {
        return \random_int(ScenarioEnv::HEADING_DEGREE_MIN, ScenarioEnv::HEADING_DEGREE_MAX);
    }

    private function getRain(): int
    {
        return \random_int(ScenarioEnv::RAIN_OFF, ScenarioEnv::RAIN_ON);
    }

    private function getVisibility(): int
    {
        return \random_int(ScenarioEnv::VISIBILITY_MIN, ScenarioEnv::VISIBILITY_MAX);
    }

    private function getRadarCondition(): int
    {
        return \random_int(ScenarioEnv::RADAR_CONDITION_MIN, ScenarioEnv::RADAR_CONDITION_MAX);
    }

    private function getAirControl(): int
    {
        return array_rand(ScenarioEnv::AIR_CONTROL);
    }

    private function getBattleType(): int
    {
        return array_rand(ScenarioEnv::BATTLE_TYPE);
    }
}
