<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\ScenarioGenerator\Engine\ContextGenerator;
use App\ScenarioGenerator\Engine\Tools;
use PHPUnit\Framework\TestCase;

class ContextGeneratorTest extends TestCase
{
    public function testGetHeaderData(): void
    {
        $tools = $this->createMock(Tools::class);
        $month = 9;
        $year = 1939;
        $scenarioName = 'Chouuu';

        $hour = 9;
        $minutes = 30;
        $hours = ['sunrise' => '08:30', 'sunset' => '19:30'];
        $sunriseHour = $hours['sunrise'];
        $sunsetHour = $hours['sunset'];
        $airSuperiority = 5;
        $visibility = 60;
        $radarCondition = 10;
        $windSpeed = 6;
        $windDirection = 240;
        $seaState = 4;
        $rain = 1;
        $battleType = 3;

        $tools->expects(static::once())->method('getHour')->willReturn($hour);
        $tools->expects(static::once())->method('getMinutes')->willReturn($minutes);
        $tools->expects(static::once())->method('getAirControl')->willReturn($airSuperiority);
        $tools->expects(static::once())->method('getVisibility')->willReturn($visibility);
        $tools->expects(static::once())->method('getRadarCondition')->willReturn($radarCondition);
        $tools->expects(static::once())->method('getWindSpeed')->willReturn($windSpeed);
        $tools->expects(static::once())->method('getWindDirection')->willReturn($windDirection);
        $tools->expects(static::once())->method('getSeaState')->willReturn($seaState);
        $tools->expects(static::once())->method('getRain')->willReturn($rain);
        $tools->expects(static::once())->method('getBattleType')->willReturn($battleType);
        $tools->expects(static::once())->method('getHours')->willReturn($hours);

        $generator = new ContextGenerator($tools);
        $header = $generator->getHeaderData($month, $year, $scenarioName);

        $expected = <<<EOT
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
        MONTH=September
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

        static::assertEquals($expected, $header);
    }
}
