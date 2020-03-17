<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Ship;

use App\Core\Exception\FileNotFoundException;
use App\Core\Exception\SideErrorException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\Core\Tas\Ship\Ship;
use App\Core\Tas\Ship\ShipExtractor;
use PHPUnit\Framework\TestCase;

class ShipExtractorTest extends TestCase
{
    public function testNormalExtraction()
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        $scenario = $repo->getOne('Bad GoebenReminiscence');
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        $extractor = new ShipExtractor($iniReader);

        $ships = $extractor->extract($scenario, 'Axis');

        static::assertEquals(2, count($ships));

        $count = 1;
        foreach ($ships as $ship) {
            static::assertInstanceOf(Ship::class, $ship);
            if (1 === $count) {
                static::assertEquals('Gneisenau', $ship->getName());
                static::assertEquals('BC', $ship->getType());
            } else {
                static::assertEquals('Scharnhorst', $ship->getName());
                static::assertEquals('BC', $ship->getType());
            }

            $count++;
        }
    }

    public function testFileDoesNotExist(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        $scenario = $repo->getOne('EmptyScenario');
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        try {
            (new ShipExtractor($iniReader))->extract($scenario, 'Axis');
        } catch (FileNotFoundException $exception) {
            static::assertEquals(
                "Impossible to read the content of the file 'tests/Assets/Tas/Scenarios/EmptyScenario/AxisShips.cfg'.",
                $exception->getMessage()
            );
        }
    }

    public function testUnknownSide(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        $scenario = $repo->getOne('EmptyScenario');
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        try {
            (new ShipExtractor($iniReader))->extract($scenario, 'Ennemy');
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ennemy'",
                $exception->getMessage()
            );
        }
    }
}
