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
        $saveGame = $this->getRepo()->getOne('Save1', true);

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

    public function testShipIsNoLongerInPort(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = $this->getRepo()->getOne('Save1', true);

        $updater->action(
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

        static::assertArrayNotHasKey('Provence', $saveGame->getNavalData()->getShipsInPort('Axis'));
        static::assertTrue($saveGame->getNavalData()->isShipsDataChanged('Axis'));
    }

    public function testDetach(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = $this->getRepo()->getOne('Save1', true);

        $updater->action(
            $saveGame,
            FleetUpdater::DETACH_ACTION,
            ['Algerie'],
            [
                'll' => 'HOP',
                'mission' => 'Intercept',
                'waypoints' => ['kjgjtll', 'HEHE'],
                'speed' => 18,
            ]
        );

        $updater->action(
            $saveGame,
            FleetUpdater::DETACH_ACTION,
            ['Roma'],
            [
                'll' => 'HOP',
                'mission' => 'Intercept',
                'waypoints' => ['kjgjtll', 'HEHE'],
                'speed' => 18,
            ]
        );

        static::assertTrue($saveGame->getNavalData()->isShipsDataChanged('Allied'));
        static::assertTrue($saveGame->getNavalData()->isShipsDataChanged('Axis'));

        static::assertEquals([
            'LOCATION' => 'kjgjtll',
            'SIDE' => 'Axis',
            'FLEET' => 'TF2',
            'DIVISION' => 'TF2DIVISION0',
        ], $saveGame->getNavalData()->getShipData('Roma'));
    }

    public function testToPort(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = $this->getRepo()->getOne('Save1', true);

        $updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Littorio'],
            [
                'port' => 'La Spezia',
            ]
        );

        $updater->action(
            $saveGame,
            FleetUpdater::TO_PORT_ACTION,
            ['Bretagne'],
            [
                'port' => 'Mers-El-Kebir',
            ]
        );

        static::assertTrue($saveGame->getNavalData()->isShipsDataChanged('Allied'));
        static::assertTrue($saveGame->getNavalData()->isShipsDataChanged('Axis'));
        static::assertArrayNotHasKey('TF1', $saveGame->getNavalData()->getFleets('Axis'));
    }

    public function testUnknownAction(): void
    {
        $portServiceMock = static::getMockBuilder(PortService::class)->disableOriginalConstructor()->getMock();
        $updater = new FleetUpdater($portServiceMock);
        $saveGame = $this->getRepo()->getOne('Save1');

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
        $saveGame = $this->getRepo()->getOne('Save1', true);

        try {
            $updater->action($saveGame, FleetUpdater::AT_SEA_ACTION, ['Provence', 'Emile Bertin']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Ships must be on the same SIDE',
                $exception->getMessage()
            );
        }

        try {
            $updater->action($saveGame, FleetUpdater::DETACH_ACTION, ['Provence', 'Emile Bertin']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Ships must be on the same SIDE',
                $exception->getMessage()
            );
        }

        try {
            $updater->action($saveGame, FleetUpdater::DETACH_ACTION, ['Roma', 'Littorio']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Ships must be on the same FLEET',
                $exception->getMessage()
            );
        }

        try {
            $updater->action($saveGame, FleetUpdater::TO_PORT_ACTION, ['Roma', 'Littorio']);
            static::fail('Since the action is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Ships must be on the same LOCATION',
                $exception->getMessage()
            );
        }
    }
}
