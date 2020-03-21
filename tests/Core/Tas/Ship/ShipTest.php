<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Ship;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Ship\Ship;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    public function testHydration(): void
    {
        $name = 'Bismarck';
        $ype = 'BB';

        $ship = new Ship($name, $ype);
        static::assertEquals($name, $ship->getName());
        static::assertEquals($ype, $ship->getType());
    }

    public function testBadType(): void
    {
        try {
            new Ship('Titanic', 'AH');
            static::fail('Since the ship type is unknown, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals("Ship type 'AH' is unknown", $exception->getMessage());
        }
    }
}
