<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\Exception\SideErrorException;
use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\Core\Tas\Exception\DuplicateShipException;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Ship\Ship as TasShip;
use PHPUnit\Framework\TestCase;

class ScenarioTest extends TestCase
{
    public function testHydration(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";
        $scenarioShipFile = 'GR.scn';

        $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);
        static::assertEquals($scenarioName, $scenario->getName());
        static::assertEquals($scenarioFullPath, $scenario->getFullPath());
    }

    public function testSetShipsWrongSide(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";
        $scenarioShipFile = 'GR.scn';

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);
            $scenario->setTasShips('Ah', []);
            static::fail('Since the side is invalid, an exception was expected');
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ah'",
                $exception->getMessage()
            );
        }
    }

    public function testSetTasShipsBadInput(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";
        $scenarioShipFile = 'GR.scn';

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);
            $scenario->setTasShips('Axis', [new \stdClass()]);
            static::fail('Since the data is not a proper TAS ship object, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Data at index #0 is not a proper TAS Ship object',
                $exception->getMessage()
            );
        }
    }

    public function testSetShipsDuplicateTasShip(): void
    {
        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $scenarioShipFile = 'GR.scn';
        $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);

        $ships = [
            new TasShip('Titanic', 'DD'),
            new TasShip('Missouri', 'BB'),
            new TasShip('Titanic', 'DD'),
        ];

        try {
            $scenario->setTasShips('Allied', $ships);
            static::fail('Since the ship entry is duplicated, an exception was expected');
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'Titanic (data at index #2)' in side 'Allied'",
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNull($exception->getPrevious());
        }

        $ships = [
            new TasShip('GrossDeutschland', 'BB'),
            new TasShip('Titanic', 'DD'),
        ];
        try {
            $scenario->setTasShips('Axis', $ships);
            static::fail('Since the ship entry is duplicated, an exception was expected');
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'Titanic (data at index #1)' in side 'Allied'",
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNull($exception->getPrevious());
        }

        $ships = [
            new TasShip('GrossDeutschland', 'BB'),
            new TasShip('Titanic', 'DD'),
        ];
        try {
            $scenario->setTasShips('Allied', $ships);
            static::fail('Since the ship entry is duplicated, an exception was expected');
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'GrossDeutschland (data at index #0)' in side 'Axis'",
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNull($exception->getPrevious());
        }
    }

    public function testSetShipsAndGetShipsNormalCase(): void
    {
        $alliedShips = [
            'Titanic' => new TasShip('Titanic', 'DD'),
            'Foch' => new TasShip('Foch', 'CA'),
        ];

        $axisShips = [
            'Bismarck' => new TasShip('Bismarck', 'BB'),
            'Tirpitz' => new TasShip('Tirpitz', 'BB'),
            'Prinz Eugen' => new TasShip('Prinz Eugen', 'CA'),
        ];

        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $shipDataFile = "C:\Tas\Scenario\Bismarck\kjljl.gg";
        $scenario = new Scenario($scenarioName, $scenarioFullPath, $shipDataFile);
        $scenario->setTasShips('Allied', $alliedShips);
        $scenario->setTasShips('Axis', $axisShips);

        static::assertEquals($alliedShips, $scenario->getTasShips('Allied'));
        static::assertEquals($axisShips, $scenario->getTasShips('Axis'));
    }

    public function testGetShipsWrongSide(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";
        $shipDataFile = "C:\Tas\Scenario\Bismarck\kjljl.gg";

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath, $shipDataFile);
            $scenario->getTasShips('Ah');
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ah'",
                $exception->getMessage()
            );
        }
    }

    public function testBasicSetGetFsShips(): void
    {
        $result = [
            'Scharnhorst' => new FsShip(
                [
                    'NAME' => 'Scharnhorst',
                    'SHORTNAME' => 'Scharnhrst',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
            'Gneisenau' => new FsShip(
                [
                    'NAME' => 'Gneisenau',
                    'SHORTNAME' => 'Gneisenau',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
        ];

        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $scenarioShipFile = 'GR.scn';
        $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);
        $scenario->setFsShips($result);
        static::assertEquals($result, $scenario->getFsShips());
    }

    public function testSetFsShipsBadInput(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";
        $scenarioShipFile = 'GR.scn';
        $fsShip = new FsShip([
            'NAME' => 'Tirpitz',
            'SHORTNAME' => 'Tirpitz',
            'TYPE' => 'BB',
            'CLASS' => 'Bismarck',
        ]);

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);
            $scenario->setFsShips([$fsShip, new \stdClass()]);
            static::fail('Since the data is not a proper FS ship object, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Data at index #1 is not a proper FS Ship object',
                $exception->getMessage()
            );
        }
    }

    public function testDuplicateFsShip(): void
    {
        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $scenarioShipFile = 'GR.scn';
        $scenario = new Scenario($scenarioName, $scenarioFullPath, $scenarioShipFile);

        $ships = [
            new FsShip(
                [
                    'NAME' => 'Scharnhorst',
                    'SHORTNAME' => 'Scharnhrst',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
            new FsShip(
                [
                    'NAME' => 'Gneisenau',
                    'SHORTNAME' => 'Gneisenau',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
            new FsShip(
                [
                    'NAME' => 'Scharnhorst',
                    'SHORTNAME' => 'Scharnhrst',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
        ];

        try {
            $scenario->setFsShips($ships);
            static::fail('Since the ship entry is duplicated, an exception was expected');
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'Scharnhorst'",
                $exception->getMessage()
            );
        }
    }
}
