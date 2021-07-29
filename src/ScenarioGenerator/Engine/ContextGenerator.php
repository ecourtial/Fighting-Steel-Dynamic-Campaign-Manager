<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

class ContextGenerator
{
    private Tools $tools;

    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    public function getHeaderData(int $month, int $year, string $scenarioName): string
    {
        $hour = $this->tools->getHour();
        $minutes = $this->tools->getMinutes();
        $monthName = ScenarioEnv::MONTHS[$month];
        $airSuperiority = $this->tools->getAirControl();
        $visibility = $this->tools->getVisibility();
        $radarCondition = $this->tools->getRadarCondition();
        $windSpeed = $this->tools->getWindSpeed();
        $windDirection = $this->tools->getWindDirection();
        $seaState = $this->tools->getSeaState();
        $rain = $this->tools->getRain();
        $battleType = $this->tools->getBattleType();

        $hours = $this->tools->getHours($month);
        $sunriseHour = $hours['sunrise'];
        $sunsetHour = $hours['sunset'];

        return <<<EOT
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
        BATTLETYPE=$battleType
        BLUE_VICTORY_MODS=1.00
        RED_VICTORY_MODS=1.00
        BLUE_FORCE_TYPE=0
        RED_FORCE_TYPE=1
        MOVIE=0
        [SCENARIOINFO]
        EOT;
    }
}
