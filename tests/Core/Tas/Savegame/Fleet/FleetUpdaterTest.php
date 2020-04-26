<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Core\Tas\Savegame\Fleet;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Savegame\Fleet\FleetExtractor;
use App\Core\Tas\Savegame\Fleet\FleetUpdater;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
use App\Core\Tas\Scenario\Scenario;
use PHPUnit\Framework\TestCase;

class FleetUpdaterTest extends TestCase
{
    /** @var SavegameRepository */
    private static $repo;

    /** @var FleetUpdater */
    private static $updater;

    public static function setUpBeforeClass(): void
    {
        $iniReader = new IniReader(new TextFileReader());

        static::$repo = new SavegameRepository(
            new SavegameReader($iniReader),
            new FleetExtractor($iniReader),
            $_ENV['TAS_LOCATION']
        );

        static::$updater = new FleetUpdater();
    }

    public function testGlobal(): void
    {
        $updater = new FleetUpdater();
        $saveGame = static::$repo->getOne('Save1', true);

        // Put Provence at sea, alone.
        $updater->action($saveGame, FleetUpdater::AT_SEA_ACTION, ['Provence']);
    }
}
