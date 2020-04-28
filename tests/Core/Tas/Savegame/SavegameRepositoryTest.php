<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\File\TextFileWriter;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\Fleet\FleetWriter;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
use App\Core\Tas\Scenario\Scenario;
use PHPUnit\Framework\TestCase;

class SavegameRepositoryTest extends TestCase
{
    /** @var SavegameRepository */
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        $iniReader = new IniReader(new TextFileReader());

        static::$repo = new SavegameRepository(
            new SavegameReader($iniReader),
            new FleetExtractor($iniReader),
            new FleetWriter(new TextFileWriter()),
            $_ENV['TAS_LOCATION']
        );
    }

    public function testGetList(): void
    {
        static::assertEquals(
            [
                'Goeben reminiscence' => 'Save1',
                'RN Nightmares' => 'Save2',
            ],
            static::$repo->getList()
        );
    }

    public function testGetOne(): void
    {
        $saveGame = static::$repo->getOne('Save1');
        static::assertEquals('Goeben reminiscence', $saveGame->getScenarioName());
        static::assertEquals('tests/Assets/TAS/Save1', $saveGame->getPath());

        static::assertEquals(0, count($saveGame->getAxisShipsInPort()));
        static::assertEquals(0, count($saveGame->getAlliedShipsInPort()));
        static::assertEquals(0, count($saveGame->getAxisFleets()));
        static::assertEquals(0, count($saveGame->getAlliedFleets()));
    }

    public function testGetOneWithAllData(): void
    {
        $saveGame = static::$repo->getOne('Save1', true);

        static::assertEquals(4, count($saveGame->getAxisShipsInPort()));
        static::assertEquals(2, count($saveGame->getAlliedShipsInPort()));
        static::assertEquals(2, count($saveGame->getAxisFleets()));
        static::assertEquals(2, count($saveGame->getAlliedFleets()));
        static::assertEquals('Tarento', $saveGame->getShipData('Provence')['LOCATION']);
        static::assertEquals('Napoli', $saveGame->getShipData('Mogador')['LOCATION']);
        static::assertEquals('Toulon', $saveGame->getShipData('Emile Bertin')['LOCATION']);
        static::assertEquals('425652N 0032723E', $saveGame->getShipData('Littorio')['LOCATION']);
    }

    public function testGetOneBadFormat(): void
    {
        try {
            static::$repo->getOne('Save7');
            static::fail('Since the input key is invalid, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Savegame key 'Save7' is not a valid format",
                $exception->getMessage()
            );
        }
    }

    /** @dataProvider persistDataProvider */
    public function testPersist(
        bool $alliedFleetModified,
        bool $axisFleetModified
    ): void {
        $iniReader = static::getMockBuilder(IniReader::class)->disableOriginalConstructor()->getMock();
        $fleetWriter = static::getMockBuilder(FleetWriter::class)->disableOriginalConstructor()->getMock();

        $saveGame = static::getMockBuilder(Savegame::class)->disableOriginalConstructor()->getMock();

        $saveGame
            ->expects(static::at(0))
            ->method('isShipsDataChanged')
            ->with(Scenario::ALLIED_SIDE)
            ->willReturn($alliedFleetModified);
        $saveGame
            ->expects(static::at(1))
            ->method('isShipsDataChanged')
            ->with(Scenario::AXIS_SIDE)
            ->willReturn($axisFleetModified);

        if ($alliedFleetModified) {
            $fleetWriter->expects(static::at(0))->method('update')->with($saveGame, Scenario::ALLIED_SIDE);
        }
        if ($axisFleetModified) {
            $at = $alliedFleetModified === true ? 1 : 0;
            $fleetWriter->expects(static::at($at))->method('update')->with($saveGame, Scenario::AXIS_SIDE);
        }

        $repo = new SavegameRepository(
            new SavegameReader($iniReader),
            new FleetExtractor($iniReader),
            $fleetWriter,
            ''
        );

        $repo->persist($saveGame);
    }

    public function persistDataProvider(): array
    {
        return [
            [true, false],
            [false, true],
            [false, false],
            [true, true],
        ];
    }
}
