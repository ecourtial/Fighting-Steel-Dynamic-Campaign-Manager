<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine\Ships;

use App\ScenarioGenerator\Engine\Ships\ShipQuantity;
use PHPUnit\Framework\TestCase;

class ShipQuantityTest extends TestCase
{
    public function testObject(): void
    {
        $shipQty = new ShipQuantity(6, 4, 2, 1);

        static::assertEquals(6, $shipQty->getAlliedTotal());
        static::assertEquals(2, $shipQty->getAlliedBig());
        static::assertEquals(4, $shipQty->getAlliedSmall());

        static::assertEquals(4, $shipQty->getAxisTotal());
        static::assertEquals(1, $shipQty->getAxisBig());
        static::assertEquals(3, $shipQty->getAxisSmall());
    }
}
