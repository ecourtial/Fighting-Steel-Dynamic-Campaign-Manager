<?php

declare(strict_types=1);

namespace App\Tests\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\NavalData;
use PHPUnit\Framework\TestCase;

class NavalDataTest extends TestCase
{
    public function testPutAtSeaBadSide(): void
    {
        $data = new NavalData();
        try {
            $data->setShipsAtSea('EH', []);
            static::fail('Since the side is invalid, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Side 'EH' is unknown",
                $exception->getMessage()
            );
        }
    }
}
