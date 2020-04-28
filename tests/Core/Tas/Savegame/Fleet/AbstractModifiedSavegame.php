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

        static::$updater = new FleetUpdater();
    }

    protected function getModifiedSavegame(): Savegame
    {
        $saveGame = static::$repo->getOne('Save1', true);

        // Put Provence at sea, alone.
        static::$updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Provence'],
            [
                'll' => 'HOP',
                'mission' => 'Intercept',
                'waypoints' => ['kjgjtll'],
                'speed' => 18,
            ]
        );

        // Put Condorcet and Mogador at sea, together
        static::$updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Condorcet', 'Mogador'],
            [
                'll' => 'HOP',
                'mission' => 'Patrol',
                'waypoints' => ['blob'],
                'speed' => 16,
            ]
        );

        // Remove Gneisenau from her division and put her in port
        static::$updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Gneisenau'],
            ['port' => 'Napoli']
        );

        // Separate Roma in her own TF: her division is disbanded
        static::$updater->action(
            $saveGame,
            FleetUpdater::DETACH_ACTION,
            ['Roma'],
            [
                'waypoints' => ['kjgjtll', 'fkljkl'],
                'mission' => 'Intercept',
                'speed' => 16,
            ]
        );

        // Put Bretagne, Lorraine, La Palme, Le Mars and Tempete in port: their TF is disbanded
        static::$updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Bretagne', 'Lorraine', 'La Palme', 'Le Mars', 'Tempete'],
            ['port' => 'Oran']
        );

        // Put Gneisenau back at sea
        static::$updater->action(
            $saveGame,
            FleetUpdater::AT_SEA_ACTION,
            ['Gneisenau'],
            [
                'll' => 'HOP',
                'waypoints' => ['hehe', 'hoho'],
                'mission' => 'Cover',
                'speed' => 24,
            ]
        );

        return $saveGame;
    }
}
