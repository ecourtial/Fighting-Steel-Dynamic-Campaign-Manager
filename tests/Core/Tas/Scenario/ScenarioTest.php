<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       14/03/2020 (dd-mm-YYYY)
 */

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
}
