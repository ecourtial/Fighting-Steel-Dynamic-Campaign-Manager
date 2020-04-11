<?php

declare(strict_types=1);
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\Core\Fs\Scenario;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\File\TextFileWriter;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\Core\Fs\Scenario\Ship\ShipExtractor;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class ScenarioUpdaterTest extends TestCase
{
    protected static ScenarioUpdater $scenarioUpdater;
    protected static ShipExtractor $extractor;

    public static function setUpBeforeClass(): void
    {
        static::$scenarioUpdater = new ScenarioUpdater(new TextFileReader(), new TextFileWriter());
        static::$extractor = new ShipExtractor(new IniReader(new TextFileReader()));
    }

    public function testUpdateBeforeFsNormal(): void
    {
        $scenarDir = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $backup = $scenarDir . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn';
        $scenario = $scenarDir . 'A_TAS_ScenarioNormalUpdate.scn';

        $correspondance = [
            'Scharnhorst' => new Ship('Scharnhorst', 'Scharnhorst', 'Scharnho1'),
            'Gneisenau' => new Ship('Gneisenau', 'Gneisenau', 'Scharnho2'),
        ];
        copy($backup, $scenario);

        static::$scenarioUpdater->updateBeforeFs($correspondance, $scenario, $backup);
        $ships = static::$extractor->extract($scenario);

        $expectedShips = [
            new FsShip(['NAME' => 'Bretagne', 'SHORTNAME' => 'Bretagne', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            new FsShip(['NAME' => 'Provence', 'SHORTNAME' => 'Provence', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            new FsShip(['NAME' => 'Le Fantasque', 'SHORTNAME' => 'La Palme', 'TYPE' => 'DD', 'CLASS' => 'Le Fantasque']),
            new FsShip(['NAME' => 'Gneisenau', 'SHORTNAME' => 'Scharnho2', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
            new FsShip(['NAME' => 'Scharnhorst', 'SHORTNAME' => 'Scharnho1', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
        ];

        static::assertEquals($expectedShips, $ships);

        unlink($scenario);
    }

    public function testUpdateAfterFs(): void
    {
        $scenarDir = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $backup = $scenarDir . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn';
        $scenario = $scenarDir . 'A_TAS_ScenarioNormalRevert.scn';

        $correspondance = [
            'Bretagne' => 'Courbet',
            'Provence' => 'Paris',
        ];
        copy($backup, $scenario);
        $content = 'SHORTNAME=AHAH' . PHP_EOL;
        $content .= 'NAME=AHAH' . PHP_EOL;
        $content .= file_get_contents($scenario);
        file_put_contents($scenario, $content);

        static::$scenarioUpdater->updateAfterFs($correspondance, $scenario);
        $ships = static::$extractor->extract($scenario);

        $expectedShips = [
            new FsShip(['NAME' => 'Courbet', 'SHORTNAME' => 'Bretagne', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            new FsShip(['NAME' => 'Paris', 'SHORTNAME' => 'Provence', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            new FsShip(['NAME' => 'Le Fantasque', 'SHORTNAME' => 'La Palme', 'TYPE' => 'DD', 'CLASS' => 'Le Fantasque']),
            new FsShip(['NAME' => 'Gneisenau', 'SHORTNAME' => 'Gneisenau', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
            new FsShip(['NAME' => 'Scharnhorst', 'SHORTNAME' => 'Scharnhors', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
        ];

        static::assertEquals($expectedShips, $ships);

        unlink($scenario);
    }
}
