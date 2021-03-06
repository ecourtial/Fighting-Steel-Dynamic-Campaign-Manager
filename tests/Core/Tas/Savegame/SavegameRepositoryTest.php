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
use App\Core\Tas\Savegame\NavalData;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
use App\Core\Tas\Scenario\Scenario;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class SavegameRepositoryTest extends TestCase
{
    /** @var SavegameRepository */
    private ?SavegameRepository $repo = null;
    private TestLogger $logger;

    private function getRepo(): SavegameRepository
    {
        if (null === $this->repo) {
            $iniReader = new IniReader(new TextFileReader());
            $this->logger = new TestLogger();

            $this->repo = new SavegameRepository(
                new SavegameReader($iniReader),
                new FleetExtractor($iniReader),
                new FleetWriter(new TextFileWriter()),
                $_ENV['TAS_LOCATION'],
                $this->logger
            );
        }

        $this->logger->reset();

        return $this->repo;
    }

    public function testGetList(): void
    {
        static::assertEquals(
            [
                'Goeben reminiscence' => 'Save1',
                'RN Nightmares' => 'Save2',
            ],
            $this->getRepo()->getList()
        );

        static::assertEquals(3, $this->logger->hasErrorRecords());
    }

    public function testGetOne(): void
    {
        $saveGame = $this->getRepo()->getOne('Save1');
        static::assertEquals('Goeben reminiscence', $saveGame->getScenarioName());
        static::assertEquals('tests/Assets/TAS/Save1', $saveGame->getPath());

        static::assertEquals(0, count($saveGame->getNavalData()->getShipsInPort('Axis')));
        static::assertEquals(0, count($saveGame->getNavalData()->getShipsInPort('Allied')));
        static::assertEquals(0, count($saveGame->getNavalData()->getFleets('Axis')));
        static::assertEquals(0, count($saveGame->getNavalData()->getFleets('Allied')));
    }

    public function testGetOneWithAllData(): void
    {
        $saveGame = $this->getRepo()->getOne('Save1', true);

        static::assertEquals(4, count($saveGame->getNavalData()->getShipsInPort('Axis')));
        static::assertEquals(2, count($saveGame->getNavalData()->getShipsInPort('Allied')));
        static::assertEquals(2, count($saveGame->getNavalData()->getFleets('Axis')));
        static::assertEquals(2, count($saveGame->getNavalData()->getFleets('Allied')));
        static::assertEquals('Tarento', $saveGame->getNavalData()->getShipData('Provence')['LOCATION']);
        static::assertEquals('Napoli', $saveGame->getNavalData()->getShipData('Mogador')['LOCATION']);
        static::assertEquals('Toulon', $saveGame->getNavalData()->getShipData('Emile Bertin')['LOCATION']);
        static::assertEquals('425652N 0032723E', $saveGame->getNavalData()->getShipData('Littorio')['LOCATION']);
    }

    public function testGetOneBadFormat(): void
    {
        try {
            $this->getRepo()->getOne('Save7');
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

        $navalData = $this->getMockBuilder(NavalData::class)->getMock();

        $navalData
            ->expects(static::at(0))
            ->method('isShipsDataChanged')
            ->with(Scenario::ALLIED_SIDE)
            ->willReturn($alliedFleetModified);
        $navalData
            ->expects(static::at(1))
            ->method('isShipsDataChanged')
            ->with(Scenario::AXIS_SIDE)
            ->willReturn($axisFleetModified);

        $saveGame = new class($navalData) extends Savegame {
            public function __construct(NavalData $navalData)
            {
                $this->navalData = $navalData;
            }
        };

        if ($alliedFleetModified) {
            $fleetWriter->expects(static::at(0))->method('update')->with($saveGame, Scenario::ALLIED_SIDE);
        }
        if ($axisFleetModified) {
            $at = true === $alliedFleetModified ? 1 : 0;
            $fleetWriter->expects(static::at($at))->method('update')->with($saveGame, Scenario::AXIS_SIDE);
        }

        $repo = new SavegameRepository(
            new SavegameReader($iniReader),
            new FleetExtractor($iniReader),
            $fleetWriter,
            '',
            new TestLogger()
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
