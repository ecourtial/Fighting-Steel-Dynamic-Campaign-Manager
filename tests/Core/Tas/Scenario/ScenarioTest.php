<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Scenario;

use App\Core\Tas\Scenario\Scenario;
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

    }

    public function testSetShipsBadInput(): void
    {

    }

    public function testSetShipsDuplicateShip(): void
    {

    }

    public function testSetShipsAndGetShipsNormalCase(): void
    {

    }

    public function testGetShipsWrongSide(): void
    {

    }
}
