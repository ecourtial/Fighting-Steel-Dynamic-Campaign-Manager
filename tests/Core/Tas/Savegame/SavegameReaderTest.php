<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\CoreException;
use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Savegame\SavegameReader;
use PHPUnit\Framework\TestCase;

class SavegameReaderTest extends TestCase
{
    /** @var \App\Core\Tas\Savegame\SavegameReader */
    private $reader;

    public function setUp(): void
    {
        $this->reader = new SavegameReader(new IniReader(new TextFileReader()));
    }

    public function testNormalReading(): void
    {
        $saveGame = $this->reader->extract($_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save1');
        static::assertFalse($saveGame->getFog());
        static::assertTrue($saveGame->getWeatherState());
        static::assertTrue($saveGame->getCloudCover());
        static::assertEquals('Goeben reminiscence', $saveGame->getScenarioName());
        static::assertEquals(193909040312, $saveGame->getSaveDate());
        static::assertEquals(312, $saveGame->getSaveTime());
    }

    public function testErrorMissingLastField(): void
    {
        try {
            $this->reader->extract($_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save3');
            static::fail('Since the last field is missing in the file, an exception was expected');
        } catch (CoreException $exception) {
            static::assertEquals(
                "Error while parsing the scenario 'tests/Assets/TAS/Save3/ScenarioInfo.cfg'",
                $exception->getMessage()
            );
        }
    }

    public function testErrorMissingField(): void
    {
        try {
            $this->reader->extract($_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Save4');
            static::fail('Since a field is missing in the file, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Error while parsing the scenario 'tests/Assets/TAS/Save4/ScenarioInfo.cfg': element 'CloudCover' is empty",
                $exception->getMessage()
            );
        }
    }
}
