<?php

declare(strict_types=1);
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\Core\Fs\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\Scenario\Ship\ShipExtractor;
use App\Core\Fs\Scenario\SideDetector;
use PHPUnit\Framework\TestCase;

class SideScenarioDetectorTest extends TestCase
{
    private static ShipExtractor $extractor;
    private static string $scenarioPath;

    public static function setUpBeforeClass(): void
    {
        static::$extractor = new ShipExtractor(new IniReader(new TextFileReader()));
        static::$scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR
            . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn';
    }

    /** @dataProvider normalTestProvider */
    public function testNormal(string $oneShip, string $expected): void
    {
        $result = (new SideDetector())->detectSide(static::$extractor->extract(static::$scenarioPath, 'RADARTYPE'), $oneShip);
        static::assertEquals($expected, $result);
    }

    public function normalTestProvider(): array
    {
        return [
            ['Provence', 'Blue'],
            ['Gneisenau', 'Red'],
        ];
    }

    public function testShipNotFound(): void
    {
        try {
            (new SideDetector())->detectSide(static::$extractor->extract(static::$scenarioPath, 'RADARTYPE'), 'Yamato');
            static::fail("Since the ship 'Yamato' does not exist, and error was expected");
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side detector error. The ship 'Yamato' is missing",
                $exception->getMessage()
            );
        }
    }
}
