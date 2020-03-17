<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Ship;

use App\Core\Tas\Ship\Ship;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    public function testHydration(): void
    {
        $name = 'Bismarck';
        $ype = "BB";

        $ship = new Ship($name, $ype);
        static::assertEquals($name, $ship->getName());
        static::assertEquals($ype, $ship->getType());
    }
}
