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
use App\Core\Fs\Ship\ShipExtractor as FsShipExtractor;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\Core\Tas\Ship\Ship;
use App\Core\Tas\Ship\ShipExtractor as TasShipExtractor;
use PHPUnit\Framework\TestCase;

class ShipExtractorTest extends TestCase
{
    protected static IniReader $iniReader;
    protected static TasShipExtractor $tasShipExtractor;
    protected static FsShipExtractor $fsShipExtractor;

    public static function setUpBeforeClass()
    {
        $textReader = new TextFileReader();
        static::$iniReader = new IniReader($textReader);
        static::$tasShipExtractor = new TasShipExtractor(static::$iniReader);
        static::$fsShipExtractor = new FsShipExtractor(static::$iniReader);
    }

    public function testNormalExtraction()
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader, static::$tasShipExtractor, static::$fsShipExtractor);
        $scenario = $repo->getOne('Bad GoebenReminiscence');
        $ships = static::$tasShipExtractor->extract($scenario, 'Axis');

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
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader, static::$tasShipExtractor, static::$fsShipExtractor);
        $scenario = $repo->getOne('IncompleteScenarioWithNotTasShipFile');
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        try {
            (new TasShipExtractor($iniReader))->extract($scenario, 'Axis');
        } catch (FileNotFoundException $exception) {
            static::assertEquals(
                "Impossible to read the content of the file 'tests/Assets/TAS/Scenarios/IncompleteScenarioWithNotTasShipFile/AxisShips.cfg'.",
                $exception->getMessage()
            );
        }
    }

    public function testUnknownSide(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader, static::$tasShipExtractor, static::$fsShipExtractor);
        $scenario = $repo->getOne('IncompleteScenarioWithNotTasShipFile');
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        try {
            (new TasShipExtractor($iniReader))->extract($scenario, 'Ennemy');
        } catch (SideErrorException $exception) {
            static::assertEquals(
                "Invalid side: 'Ennemy'",
                $exception->getMessage()
            );
        }
    }
}
