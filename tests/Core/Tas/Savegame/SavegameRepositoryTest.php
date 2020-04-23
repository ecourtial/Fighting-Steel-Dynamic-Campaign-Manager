<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Tas\Savegame\SavegameReader;
use App\Core\Tas\Savegame\SavegameRepository;
use PHPUnit\Framework\TestCase;

class SavegameRepositoryTest extends TestCase
{
    /** @var SavegameRepository */
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        static::$repo = new SavegameRepository(
            new SavegameReader(new IniReader(new TextFileReader())),
            $_ENV['TAS_LOCATION']
        );
    }

    public function testGetList(): void
    {
        static::assertEquals(
            [
                'Goeben reminiscence' => 'Save1',
                'RN Nightmares' => 'Save2',
            ],
            static::$repo->getList()
        );
    }

    public function testGetOne(): void
    {
        $saveGame = static::$repo->getOne('Save1');
        static::assertEquals('Goeben reminiscence', $saveGame->getScenarioName());
    }

    public function testGetOneWithAllData(): void
    {
        
    }

    public function testGetOneBadFormat(): void
    {
        try {
            static::$repo->getOne('Save7');
            static::fail('Since the input key is invalid, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Savegame key 'Save7' is not a valid format",
                $exception->getMessage()
            );
        }
    }
}
