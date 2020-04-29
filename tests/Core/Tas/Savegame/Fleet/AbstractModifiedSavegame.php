<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame\Fleet;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\File\TextFileWriter;
use App\Core\Tas\Port\PortService;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\Fleet\FleetUpdater;
use App\Core\Tas\Savegame\Fleet\FleetWriter;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
use PHPUnit\Framework\TestCase;

abstract class AbstractModifiedSavegame extends TestCase
{
    /** @var SavegameRepository */
    protected static $repo;

    /** @var FleetUpdater */
    protected static $updater;

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

    protected function getModifiedSavegame(): Savegame
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $portServiceMock->method('getPortFirstWaypoint')->willReturn('AHAH');

        $updater = new FleetUpdater($portServiceMock);

        $saveGame = static::$repo->getOne('Save1', true);

        // Put Provence at sea, alone.
        $updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Provence'],
            [
                'mission' => 'Intercept',
                'waypoints' => ['kjgjtll', 'kjgergtrggjtll'],
                'speed' => 18,
            ]
        );

        // Put Condorcet and Mogador at sea, together
        $updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Condorcet', 'Mogador'],
            [
                'mission' => 'Patrol',
                'waypoints' => ['blob', 'kjgergtrggjtll'],
                'speed' => 16,
            ]
        );

        static::assertEquals([
            'LOCATION' => 'AHAH',
            'SIDE' => 'Axis',
            'FLEET' => 'TF3',
            'DIVISION' => 'TF3DIVISION0',
        ], $saveGame->getShipData('Mogador'));

        // Remove Gneisenau from her division and put her in port
        $updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Gneisenau'],
            ['port' => 'Napoli']
        );

        // Separate Roma in her own TF: her division is disbanded
        $updater->action(
            $saveGame,
            FleetUpdater::DETACH_ACTION,
            ['Roma'],
            [
                'waypoints' => ['kjgjtll', 'fkljkl'],
                'mission' => 'Intercept',
                'speed' => 16,
            ]
        );

        static::assertEquals([
            'LOCATION' => 'kjgjtll',
            'SIDE' => 'Axis',
            'FLEET' => 'TF4',
            'DIVISION' => 'TF4DIVISION0',
            ], $saveGame->getShipData('Roma'));

        // Detach Algerie
        $updater->action(
            $saveGame,
            FleetUpdater::DETACH_ACTION,
            ['Algerie'],
            [
                'waypoints' => ['kjgjtll', 'fkljkl'],
                'mission' => 'Bombard',
                'speed' => 16,
            ]
        );

        $newAlgerieData = [
            'LOCATION' => 'kjgjtll',
            'SIDE' => 'Allied',
            'FLEET' => 'TF2',
            'DIVISION' => 'TF2DIVISION0',
        ];
        static::assertEquals($newAlgerieData, $saveGame->getShipData('Algerie'));
        static::assertEquals(
            [
                'Algerie' => [
                    'TYPE' => 'CA',
                    'MAXSPEED' => '32',
                    'ENDURANCE' => '195',
                    'CURRENTENDURANCE' => '186',
                    'RECONRANGE' => '0',
                ],
            ],
            $saveGame->getFleets('Allied')['TF2']->getDivisions()['TF2DIVISION0']
        );
        static::assertEquals(['kjgjtll', 'fkljkl'], $saveGame->getFleets('Allied')['TF2']->getWaypoints());
        static::assertEquals(
            ['Dupleix', 'Foch'],
            array_keys($saveGame->getFleets('Allied')['TF1']->getDivisions()['TF1DIVISION0'])
        );

        // Put Bretagne, Lorraine, La Palme, Le Mars and Tempete in port: their TF is disbanded
        $updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Bretagne', 'Lorraine', 'La Palme', 'Le Mars', 'Tempete'],
            ['port' => 'Oran']
        );

        // Put Gneisenau back at sea
        $updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Gneisenau'],
            [
                'waypoints' => ['hehe', 'hoho', 'kjgergtrggjtll'],
                'mission' => 'Cover',
                'speed' => 24,
            ]
        );

        // Check before return
        static::assertTrue($saveGame->isShipsDataChanged('Axis'));
        static::assertTrue($saveGame->isShipsDataChanged('Allied'));

        return $saveGame;
    }
}
