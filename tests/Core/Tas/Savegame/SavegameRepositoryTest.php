<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
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
        static::assertEquals(12, count($saveGame->getAlliedShipsInPort()));
        static::assertEquals(2, count($saveGame->getAxisFleets()));
        static::assertEquals(2, count($saveGame->getAlliedFleets()));
        static::assertEquals('Tarento', $saveGame->getShipData('Provence')['location']);
        static::assertEquals('Napoli', $saveGame->getShipData('Mogador')['location']);
        static::assertEquals('Toulon', $saveGame->getShipData('Emile Bertin')['location']);
        static::assertEquals('425652N 0032723E', $saveGame->getShipData('Littorio')['location']);
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

    public function testPersist(): void
    {

    }
}
