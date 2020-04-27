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
use App\Core\Tas\Map\MapService;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\Fleet\FleetUpdater;
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
        $mapService = new MapService();

        static::$repo = new SavegameRepository(
            new SavegameReader($iniReader),
            new FleetExtractor($iniReader),
            $_ENV['TAS_LOCATION']
        );

        static::$updater = new FleetUpdater($mapService);
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
                'mission' => 'Patrol',
                'waypoints' => ['blob'],
                'speed' => 16,
            ]
        );

        return $saveGame;
    }
}
