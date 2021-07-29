<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\Core\File\TextFileReader;
use App\ScenarioGenerator\Engine\BodyGenerator;
use App\ScenarioGenerator\Engine\CoordinatesCalculator;
use App\ScenarioGenerator\Engine\Ships\ShipQuantity;
use App\ScenarioGenerator\Engine\ShipsSelector;
use App\ScenarioGenerator\Engine\Tools;
use PHPUnit\Framework\TestCase;

class BodyGeneratorTest extends TestCase
{
    private static ShipsSelector $shipsSelector;
    private static TextFileReader $textFileReader;
    private static CoordinatesCalculator $coordinatesCalculator;
    private static Tools $tools;

    protected function setUp(): void
    {
        static::$shipsSelector = static::createMock(ShipsSelector::class);
        static::$textFileReader = new TextFileReader();
        static::$tools = static::createMock(Tools::class);
        static::$coordinatesCalculator = new CoordinatesCalculator(static::$tools);
    }

    public function testGetBody(): void
    {
        $code = 'Atlantic';
        $period = 1;
        $year = 1940;
        $mixedNavies = true;
        $alliedShipQty = 2;
        $axisShipQty = 2;
        $alliedBig = 1;
        $axisBig = 1;

        static::$tools->expects(static::exactly(2))->method('getRandomShipQty')
            ->willReturn($alliedShipQty, $axisShipQty);

        static::$tools->expects(static::exactly(2))->method('getBigShipCount')
            ->withConsecutive([$alliedShipQty], [$axisShipQty])
            ->willReturnOnConsecutiveCalls($alliedBig, $axisBig);

        static::$tools->expects(static::exactly(2))->method('getRandomHeading')
            ->willReturnOnConsecutiveCalls(180, 270);

        static::$shipsSelector->expects(static::once())->method('getShips')
           ->with(
               $code,
               $period,
               new ShipQuantity($alliedShipQty, $axisShipQty, $alliedBig, $axisBig),
               $mixedNavies
           )
           ->willReturn([
               'Allied' => $this->getAlliedShips(),
               'Axis' => $this->getAxisShips(),
           ]);

        static::$tools->method('getRandomCrewQuality')
            ->willReturn('Green');

        static::$tools->method('getRandomCrewFatigue')
            ->willReturn('Tired');

        static::$tools->method('getRandomCrewNightTraining')
            ->willReturn('Average');

        static::$tools->method('getRandomRadarLevel')
            ->willReturn('Average');

        $generator = new BodyGenerator(
            self::$shipsSelector,
            self::$textFileReader,
            self::$coordinatesCalculator,
            self::$tools,
            '.'
        );

        $result = $generator->getBody($code, $period, $year, $mixedNavies);

        // Normal use case: there are BB and DD in both sides
        static::assertEquals($this->getExpectedResult(), $result);
    }

    public function testNoSmallShips(): void
    {
        // Special case when one side (or both) does not have DD
        $code = 'Atlantic';
        $period = 1;
        $year = 1940;
        $mixedNavies = true;
        $alliedShipQty = 2;
        $axisShipQty = 2;
        $alliedBig = 2;
        $axisBig = 2;

        static::$tools->expects(static::exactly(2))->method('getRandomShipQty')
            ->willReturn($alliedShipQty, $axisShipQty);

        static::$tools->expects(static::exactly(2))->method('getBigShipCount')
            ->withConsecutive([$alliedShipQty], [$axisShipQty])
            ->willReturnOnConsecutiveCalls($alliedBig, $axisBig);

        $generator = new BodyGenerator(
            self::$shipsSelector,
            self::$textFileReader,
            self::$coordinatesCalculator,
            self::$tools,
            '.'
        );

        $result = $generator->getBody($code, $period, $year, $mixedNavies);
        static::assertEquals(0, strpos($result, 'DIVISIONCNT=2'));
    }

    private function getAlliedShips(): array
    {
        return [
            'RN' => [
                [
                    'name' => 'Nelson',
                    'class' => 'Nelson',
                    'type' => 'BB',
                ],
                [
                    'name' => 'Amazon',
                    'class' => 'AB Class',
                    'type' => 'DD',
                ],
            ],
        ];
    }

    private function getAxisShips(): array
    {
        return [
            'KM' => [
                [
                    'name' => 'Bismarck',
                    'class' => 'Bismarck',
                    'type' => 'BB',
                ],
                [
                    'name' => 'Theodor Riedel',
                    'class' => '1934A Type',
                    'type' => 'DD',
                ],
            ],
        ];
    }

    private function getExpectedResult(): string
    {
        return <<<EOT
        DIVISIONCNT=4

        [DIVISION0]
        DIVISIONNAME=Division 0
        SIDE=Blue
        FORMATION=Column
        FORMATIONHEADING=180
        FORMATIONSPACING=500
        SPEED=16
        SHIPCNT=1
        FLAGSHIPINDEX=0
        ENCUMBERED=0
        [DIVISION0SHIP0]
        NAME=Nelson
        SHORTNAME=Nelson
        TYPE=BB
        CLASS=Nelson
        NAVY=RN
        XPOSITION=40000
        YPOSITION=0.000000
        ZPOSITION=40000
        CREWQUALITY=Green
        CREWFATIGUE=Tired
        NIGHTTRAINING=Average
        PREVIOUSCOMBAT=None
        CURRENTHITPOINTS=29545
        CURRENTFLOATPOINTS=14773
        CURRENTFIRECOUNT=0
        PROPULSIONSYSTEMPERCENTAGE=100
        PROPULSIONSYSTEMDAMAGE=0
        MANUEVERSYSTEMDIRECTION=Center
        MANUEVERSYSTEMDAMAGE=0
        RADARTYPE=Average
        RADARDAMAGE=0
        ELECTRICALSYSTEMDAMAGE=0
        BRIDGEDAMAGE=0
        DAMAGECONTROLPERCENTAGE=100
        DAMAGECONTROLDAMAGE=0
        SEARCHLIGHTP1DAMAGE=0
        SEARCHLIGHTP2DAMAGE=0
        SEARCHLIGHTS1DAMAGE=0
        SEARCHLIGHTS2DAMAGE=0
        CIWPERCENTAGE=100
        CIWDAMAGE=0
        DIRECTOR1DAMAGE=0
        DIRECTOR2DAMAGE=0
        TORPMOUNT1TORPSLEFT=0
        TORPMOUNT1DAMAGE=0
        TORPMOUNT2TORPSLEFT=0
        TORPMOUNT2DAMAGE=0
        TORPMOUNT3TORPSLEFT=0
        TORPMOUNT3DAMAGE=0
        TORPMOUNT4TORPSLEFT=0
        TORPMOUNT4DAMAGE=0
        SECONDARYGUNSFORECNT=0
        SECONDARYGUNSAFTCNT=0
        SECONDARYGUNSPORTCNT=3
        SECONDARYGUNSSTBCNT=3
        SECONDARYMAGDAMAGE=0
        SECONDARYMAGHE=900
        SECONDARYMAGCOM=900
        SECONDARYMAGAP=0
        MGTADAMAGE=0
        MGTBDAMAGE=0
        MGTCDAMAGE=0
        MGTDDAMAGE=0
        MGTEDAMAGE=0
        MGTGDAMAGE=0
        MGTHDAMAGE=0
        MGTIDAMAGE=0
        MGTJDAMAGE=0
        MGTLDAMAGE=0
        MGTMDAMAGE=0
        MGTNDAMAGE=0
        MGTODAMAGE=0
        MGTQDAMAGE=0
        MGTRDAMAGE=0
        MGTTDAMAGE=0
        MGTUDAMAGE=0
        MGTXDAMAGE=0
        MGTYDAMAGE=0
        MGTZDAMAGE=0
        MAGADAMAGE=0
        MAGAHE=200
        MAGAAP=700
        MAGACOM=0
        MAGBDAMAGE=0
        MAGBHE=0
        MAGBAP=0
        MAGBCOM=0
        YARDSXPOSITION=4000
        YARDSZPOSITION=4000

        [DIVISION1]
        DIVISIONNAME=Division 1
        SIDE=Blue
        FORMATION=Column
        FORMATIONHEADING=180
        FORMATIONSPACING=500
        SPEED=16
        SHIPCNT=1
        FLAGSHIPINDEX=0
        ENCUMBERED=0
        [DIVISION1SHIP0]
        NAME=Amazon
        SHORTNAME=Amazon
        TYPE=DD
        CLASS=AB Class
        NAVY=RN
        XPOSITION=40000
        YPOSITION=0.000000
        ZPOSITION=40500
        CREWQUALITY=Green
        CREWFATIGUE=Tired
        NIGHTTRAINING=Average
        PREVIOUSCOMBAT=None
        CURRENTHITPOINTS=1815
        CURRENTFLOATPOINTS=1389
        CURRENTFIRECOUNT=0
        PROPULSIONSYSTEMPERCENTAGE=100
        PROPULSIONSYSTEMDAMAGE=0
        MANUEVERSYSTEMDIRECTION=Center
        MANUEVERSYSTEMDAMAGE=0
        RADARTYPE=Average
        RADARDAMAGE=0
        ELECTRICALSYSTEMDAMAGE=0
        BRIDGEDAMAGE=0
        DAMAGECONTROLPERCENTAGE=100
        DAMAGECONTROLDAMAGE=0
        SEARCHLIGHTP1DAMAGE=0
        SEARCHLIGHTP2DAMAGE=0
        SEARCHLIGHTS1DAMAGE=0
        SEARCHLIGHTS2DAMAGE=0
        CIWPERCENTAGE=100
        CIWDAMAGE=0
        DIRECTOR1DAMAGE=0
        DIRECTOR2DAMAGE=0
        TORPMOUNT1TORPSLEFT=4
        TORPMOUNT1DAMAGE=0
        TORPMOUNT2TORPSLEFT=4
        TORPMOUNT2DAMAGE=0
        TORPMOUNT3TORPSLEFT=0
        TORPMOUNT3DAMAGE=0
        TORPMOUNT4TORPSLEFT=0
        TORPMOUNT4DAMAGE=0
        SECONDARYGUNSFORECNT=0
        SECONDARYGUNSAFTCNT=0
        SECONDARYGUNSPORTCNT=0
        SECONDARYGUNSSTBCNT=0
        SECONDARYMAGDAMAGE=0
        SECONDARYMAGHE=0
        SECONDARYMAGCOM=0
        SECONDARYMAGAP=0
        MGTADAMAGE=0
        MGTBDAMAGE=0
        MGTCDAMAGE=0
        MGTDDAMAGE=0
        MGTEDAMAGE=0
        MGTGDAMAGE=0
        MGTHDAMAGE=0
        MGTIDAMAGE=0
        MGTJDAMAGE=0
        MGTLDAMAGE=0
        MGTMDAMAGE=0
        MGTNDAMAGE=0
        MGTODAMAGE=0
        MGTQDAMAGE=0
        MGTRDAMAGE=0
        MGTTDAMAGE=0
        MGTUDAMAGE=0
        MGTXDAMAGE=0
        MGTYDAMAGE=0
        MGTZDAMAGE=0
        MAGADAMAGE=0
        MAGAHE=0
        MAGAAP=0
        MAGACOM=380
        MAGBDAMAGE=0
        MAGBHE=0
        MAGBAP=0
        MAGBCOM=380
        YARDSXPOSITION=4000
        YARDSZPOSITION=4500

        [DIVISION2]
        DIVISIONNAME=Division 2
        SIDE=Red
        FORMATION=Column
        FORMATIONHEADING=270
        FORMATIONSPACING=500
        SPEED=16
        SHIPCNT=1
        FLAGSHIPINDEX=0
        ENCUMBERED=0
        [DIVISION2SHIP0]
        NAME=Bismarck
        SHORTNAME=Bismarck
        TYPE=BB
        CLASS=Bismarck
        NAVY=KM
        XPOSITION=40000
        YPOSITION=0.000000
        ZPOSITION=40000
        CREWQUALITY=Green
        CREWFATIGUE=Tired
        NIGHTTRAINING=Average
        PREVIOUSCOMBAT=None
        CURRENTHITPOINTS=36704
        CURRENTFLOATPOINTS=18352
        CURRENTFIRECOUNT=0
        PROPULSIONSYSTEMPERCENTAGE=100
        PROPULSIONSYSTEMDAMAGE=0
        MANUEVERSYSTEMDIRECTION=Center
        MANUEVERSYSTEMDAMAGE=0
        RADARTYPE=Average
        RADARDAMAGE=0
        ELECTRICALSYSTEMDAMAGE=0
        BRIDGEDAMAGE=0
        DAMAGECONTROLPERCENTAGE=100
        DAMAGECONTROLDAMAGE=0
        SEARCHLIGHTP1DAMAGE=0
        SEARCHLIGHTP2DAMAGE=0
        SEARCHLIGHTS1DAMAGE=0
        SEARCHLIGHTS2DAMAGE=0
        CIWPERCENTAGE=100
        CIWDAMAGE=0
        DIRECTOR1DAMAGE=0
        DIRECTOR2DAMAGE=0
        TORPMOUNT1TORPSLEFT=0
        TORPMOUNT1DAMAGE=0
        TORPMOUNT2TORPSLEFT=0
        TORPMOUNT2DAMAGE=0
        TORPMOUNT3TORPSLEFT=0
        TORPMOUNT3DAMAGE=0
        TORPMOUNT4TORPSLEFT=0
        TORPMOUNT4DAMAGE=0
        SECONDARYGUNSFORECNT=0
        SECONDARYGUNSAFTCNT=0
        SECONDARYGUNSPORTCNT=3
        SECONDARYGUNSSTBCNT=3
        SECONDARYMAGDAMAGE=0
        SECONDARYMAGHE=900
        SECONDARYMAGCOM=0
        SECONDARYMAGAP=900
        MGTADAMAGE=0
        MGTBDAMAGE=0
        MGTCDAMAGE=0
        MGTDDAMAGE=0
        MGTEDAMAGE=0
        MGTGDAMAGE=0
        MGTHDAMAGE=0
        MGTIDAMAGE=0
        MGTJDAMAGE=0
        MGTLDAMAGE=0
        MGTMDAMAGE=0
        MGTNDAMAGE=0
        MGTODAMAGE=0
        MGTQDAMAGE=0
        MGTRDAMAGE=0
        MGTTDAMAGE=0
        MGTUDAMAGE=0
        MGTXDAMAGE=0
        MGTYDAMAGE=0
        MGTZDAMAGE=0
        MAGADAMAGE=0
        MAGAHE=142
        MAGAAP=190
        MAGACOM=142
        MAGBDAMAGE=0
        MAGBHE=142
        MAGBAP=190
        MAGBCOM=142
        YARDSXPOSITION=4000
        YARDSZPOSITION=4000

        [DIVISION3]
        DIVISIONNAME=Division 3
        SIDE=Red
        FORMATION=Column
        FORMATIONHEADING=270
        FORMATIONSPACING=500
        SPEED=16
        SHIPCNT=1
        FLAGSHIPINDEX=0
        ENCUMBERED=0
        [DIVISION3SHIP0]
        NAME=Theodor Riedel
        SHORTNAME=Theodor Ri
        TYPE=DD
        CLASS=1934A Type
        NAVY=KM
        XPOSITION=40500
        YPOSITION=0.000000
        ZPOSITION=40000
        CREWQUALITY=Green
        CREWFATIGUE=Tired
        NIGHTTRAINING=Average
        PREVIOUSCOMBAT=None
        CURRENTHITPOINTS=3165
        CURRENTFLOATPOINTS=2220
        CURRENTFIRECOUNT=0
        PROPULSIONSYSTEMPERCENTAGE=100
        PROPULSIONSYSTEMDAMAGE=0
        MANUEVERSYSTEMDIRECTION=Center
        MANUEVERSYSTEMDAMAGE=0
        RADARTYPE=Average
        RADARDAMAGE=0
        ELECTRICALSYSTEMDAMAGE=0
        BRIDGEDAMAGE=0
        DAMAGECONTROLPERCENTAGE=100
        DAMAGECONTROLDAMAGE=0
        SEARCHLIGHTP1DAMAGE=0
        SEARCHLIGHTP2DAMAGE=0
        SEARCHLIGHTS1DAMAGE=0
        SEARCHLIGHTS2DAMAGE=0
        CIWPERCENTAGE=100
        CIWDAMAGE=0
        DIRECTOR1DAMAGE=0
        DIRECTOR2DAMAGE=0
        TORPMOUNT1TORPSLEFT=4
        TORPMOUNT1DAMAGE=0
        TORPMOUNT2TORPSLEFT=4
        TORPMOUNT2DAMAGE=0
        TORPMOUNT3TORPSLEFT=0
        TORPMOUNT3DAMAGE=0
        TORPMOUNT4TORPSLEFT=0
        TORPMOUNT4DAMAGE=0
        SECONDARYGUNSFORECNT=0
        SECONDARYGUNSAFTCNT=0
        SECONDARYGUNSPORTCNT=0
        SECONDARYGUNSSTBCNT=0
        SECONDARYMAGDAMAGE=0
        SECONDARYMAGHE=0
        SECONDARYMAGCOM=0
        SECONDARYMAGAP=0
        MGTADAMAGE=0
        MGTBDAMAGE=0
        MGTCDAMAGE=0
        MGTDDAMAGE=0
        MGTEDAMAGE=0
        MGTGDAMAGE=0
        MGTHDAMAGE=0
        MGTIDAMAGE=0
        MGTJDAMAGE=0
        MGTLDAMAGE=0
        MGTMDAMAGE=0
        MGTNDAMAGE=0
        MGTODAMAGE=0
        MGTQDAMAGE=0
        MGTRDAMAGE=0
        MGTTDAMAGE=0
        MGTUDAMAGE=0
        MGTXDAMAGE=0
        MGTYDAMAGE=0
        MGTZDAMAGE=0
        MAGADAMAGE=0
        MAGAHE=0
        MAGAAP=0
        MAGACOM=360
        MAGBDAMAGE=0
        MAGBHE=0
        MAGBAP=0
        MAGBCOM=240
        YARDSXPOSITION=4500
        YARDSZPOSITION=4000


        EOT;
    }
}
