<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Savegame;
use PHPUnit\Framework\TestCase;

class SavegameTest extends TestCase
{
    private $input = [
        'Fog' => 'Yes',
        'ScenarioName' => 'Eric',
        'SaveDate' => '19390903',
        'SaveTime' => '1205',
        'CloudCover' => '1',
        'WeatherState' => '0',
    ];

    public function testNormalInput(): void
    {
        $save = new Savegame($this->input);
        static::assertEquals(true, $save->getFog());
        static::assertEquals('Eric', $save->getScenarioName());
        static::assertEquals('19390903', $save->getSaveDate());
        static::assertEquals('1205', $save->getSaveTime());
        static::assertEquals(true, $save->getCloudCover());
        static::assertEquals(false, $save->getWeatherState());
    }

    public function testBadFog(): void
    {
        $input = $this->input;
        $input['Fog'] = 'Foo';
        try {
            $save = new Savegame($input);
            static::fail('Since the fog entry is wrong, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Invalid fog entry: 'Foo'",
                $exception->getMessage()
            );
        }
    }

    public function testHydrateError(): void
    {
        $input = $this->input;
        unset($input['Fog']);

        try {
            new Savegame($input);
            static::fail('Since the input data is incomplete, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Invalid attribute quantity in App\Core\Tas\Savegame\Savegame',
                $exception->getMessage()
            );
        }
    }
}
