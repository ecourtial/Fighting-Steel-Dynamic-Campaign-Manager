<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame\Fleet;

use App\Core\File\TextFileWriter;
use App\Core\Tas\Savegame\Fleet\FleetWriter;

class FleetWriterTest extends AbstractModifiedSavegame
{
    protected static FleetWriter $fleetWriter;
    protected static array $targetFiles = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$fleetWriter = new FleetWriter(new TextFileWriter());

        $sourceDir = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR;

        $file = $sourceDir . 'Save5' . DIRECTORY_SEPARATOR . 'ScenarioInfo.cfg';
        static::$targetFiles[] = $file;
        copy($sourceDir . 'Save1' . DIRECTORY_SEPARATOR . 'ScenarioInfo.cfg', $file);

        $file = $sourceDir . 'Save5' . DIRECTORY_SEPARATOR . 'AlliedShips.cfg';
        static::$targetFiles[] = $file;
        copy($sourceDir . 'Save1' . DIRECTORY_SEPARATOR . 'AlliedShips.cfg', $file);

        $file = $sourceDir . 'Save5' . DIRECTORY_SEPARATOR . 'AxisShips.cfg';
        static::$targetFiles[] = $file;
        copy($sourceDir . 'Save1' . DIRECTORY_SEPARATOR . 'AxisShips.cfg', $file);
    }

    public static function tearDownAfterClass(): void
    {
        foreach (static::$targetFiles as $file) {
            unlink($file);
        }
    }

    public function testUpdateAxisFleet(): void
    {
        // Change the path just to not override the real one.
        $saveGame = $this->getModifiedSavegame();
        $saveGame->setPath($_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save5');

        static::$fleetWriter->update($saveGame, 'Axis');
        static::$fleetWriter->update($saveGame, 'Allied');

        // Mock the state that it should be after reload
        $saveGame->setShipsDataChanged('Axis', false);
        $saveGame->setShipsDataChanged('Allied', false);

        $reloadSaveGame = static::$repo->getOne('Save5', true);

        static::assertEquals($saveGame, $reloadSaveGame);
    }
}
