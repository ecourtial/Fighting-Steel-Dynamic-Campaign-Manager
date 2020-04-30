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

    // Note: this code also test a big part of the FleetUpdater class
    public function testUpdateAxisFleet(): void
    {
        // Change the path just to not override the real one.
        $saveGame = $this->getModifiedSavegame();
        $path = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save5';
        $saveGame->setPath($path);

        static::$fleetWriter->update($saveGame, 'Axis');
        static::$fleetWriter->update($saveGame, 'Allied');

        // Mock the state that it should be after reload
        $saveGame->getNavalData()->setShipsDataChanged('Axis', false);
        $saveGame->getNavalData()->setShipsDataChanged('Allied', false);

        $reloadSaveGame = $this->getRepo()->getOne('Save5', true);

        // So now we compare the savegame before persist and after reload
        static::assertEquals($saveGame, $reloadSaveGame);

        // And now we also need to check the file is properly written
        $expectedContentDir = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'ExpectedModification' . DIRECTORY_SEPARATOR . 'Save1'
            . DIRECTORY_SEPARATOR;

        $expectedContent = file_get_contents($expectedContentDir . 'AxisShips.cfg');
        $newContent = file_get_contents($path . DIRECTORY_SEPARATOR . 'AxisShips.cfg');
        static::assertEquals($expectedContent, $newContent);

        $expectedContent = file_get_contents($expectedContentDir . 'AlliedShips.cfg');
        $newContent = file_get_contents($path . DIRECTORY_SEPARATOR . 'AlliedShips.cfg');
        static::assertEquals($expectedContent, $newContent);
    }
}
