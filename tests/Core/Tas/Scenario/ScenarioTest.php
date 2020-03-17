<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\Exception\SideErrorException;
use App\Core\Tas\Exception\DuplicateShipException;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Ship\Ship;
use PHPUnit\Framework\TestCase;

class ScenarioTest extends TestCase
{
    public function testHydration(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";

        $scenario = new Scenario($scenarioName, $scenarioFullPath);
        static::assertEquals($scenarioName, $scenario->getName());
        static::assertEquals($scenarioFullPath, $scenario->getFullPath());
    }

    public function testSetShipsWrongSide(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath);
            $scenario->setShips('Ah', []);
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ah'",
                $exception->getMessage()
            );
        }
    }

    public function testSetShipsBadInput(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath);
            $scenario->setShips('Axis', [new \stdClass()]);
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Data at index #0 is not a proper Ship object',
                $exception->getMessage()
            );
        }
    }

    public function testSetShipsDuplicateShip(): void
    {
        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $scenario = new Scenario($scenarioName, $scenarioFullPath);

        $ships = [
            new Ship('Titanic', 'Liner'),
            new Ship('Missouri', 'BB'),
            new Ship('Titanic', 'Liner'),
        ];

        try {
            $scenario->setShips('Allied', $ships);
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'Titanic' in side 'Allied'",
                $exception->getMessage()
            );
        }

        $ships = [
            new Ship('GrossDeutschland', 'BB'),
            new Ship('Titanic', 'Liner'),
        ];
        try {
            $scenario->setShips('Axis', $ships);
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'Titanic' in side 'Allied'",
                $exception->getMessage()
            );
        }

        $ships = [
            new Ship('GrossDeutschland', 'BB'),
            new Ship('Titanic', 'Liner'),
        ];
        try {
            $scenario->setShips('Allied', $ships);
        } catch (DuplicateShipException $exception) {
            static::assertEquals(
                "Duplicate ship entry with name 'GrossDeutschland' in side 'Axis'",
                $exception->getMessage()
            );
        }
    }

    public function testSetShipsAndGetShipsNormalCase(): void
    {
        $alliedShips = [
            'Titanic' => new Ship('Titanic', 'Liner'),
            'Foch' => new Ship('Foch', 'CA'),
        ];

        $axisShips = [
            'Bismarck' => new Ship('Bismarck', 'BB'),
            'Tirpitz' => new Ship('Tirpitz', 'BB'),
            'Prinz Eugen' => new Ship('Prinz Eugen', 'CA'),
        ];

        $scenarioName = 'Iceberg';
        $scenarioFullPath = "C:\Tas\Scenario\Iceberg";
        $scenario = new Scenario($scenarioName, $scenarioFullPath);
        $scenario->setShips('Allied', $alliedShips);
        $scenario->setShips('Axis', $axisShips);

        static::assertEquals($alliedShips, $scenario->getShips('Allied'));
        static::assertEquals($axisShips, $scenario->getShips('Axis'));
    }

    public function testGetShipsWrongSide(): void
    {
        $scenarioName = 'Bismarck';
        $scenarioFullPath = "C:\Tas\Scenario\Bismarck";

        try {
            $scenario = new Scenario($scenarioName, $scenarioFullPath);
            $scenario->getShips('Ah');
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ah'",
                $exception->getMessage()
            );
        }
    }
}
