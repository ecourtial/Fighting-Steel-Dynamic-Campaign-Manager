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
use Psr\Log\Test\TestLogger;

abstract class AbstractModifiedSavegame extends TestCase
{
    /** @var SavegameRepository */
    private ?SavegameRepository $repo = null;
    private TestLogger $logger;

    protected function getRepo(): SavegameRepository
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

    protected function getModifiedSavegame(): Savegame
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $portServiceMock->method('getPortFirstWaypoint')->willReturn('AHAH');

        $updater = new FleetUpdater($portServiceMock);

        $saveGame = $this->getRepo()->getOne('Save1', true);

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

        return $saveGame;
    }
}
