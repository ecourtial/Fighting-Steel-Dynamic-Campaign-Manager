<?php

declare(strict_types=1);

namespace App\Tests\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Port\PortService;
use App\Core\Tas\Savegame\Fleet\FleetUpdater;

// Note that a big part of the code of the FleetUpdater class is actually tested in the FleetWriterTest
class FleetUpdaterTest extends AbstractModifiedSavegame
{
    public function testShipsNotOnTheSameSideFail(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = static::$repo->getOne('Save1', true);

        // Put Provence at sea, alone.
        try {
            $updater->action(
                $saveGame,
                FleetUpdater::AT_SEA_ACTION,
                ['Provence', 'Emile Bertin'],
                [
                    'll' => 'HOP',
                    'mission' => 'Intercept',
                    'waypoints' => ['kjgjtll'],
                    'speed' => 18,
                ]
            );
            static::fail('Since the ships are not on the same side, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Ships must be on the same SIDE',
                $exception->getMessage()
            );
        }
    }

    public function testUnknownAction(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = static::$repo->getOne('Save1');

        try {
            $updater->action($saveGame, 'AHAH', ['Hood']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Unknown ship action: 'AHAH'",
                $exception->getMessage()
            );
        }
    }

    public function testNotSameData(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = static::$repo->getOne('Save1', true);

        try {
            $updater->action($saveGame, FleetUpdater::AT_SEA_ACTION, ['Provence', 'Emile Bertin']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Ships must be on the same SIDE",
                $exception->getMessage()
            );
        }
    }
}
