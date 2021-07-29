<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine;

use App\ScenarioGenerator\Engine\SideGenerator;
use PHPUnit\Framework\TestCase;

class SideGeneratorTest extends TestCase
{
    public function testGetSidesNotMixedNavies(): void
    {
        $generator = new SideGenerator();
        $result = $generator->getSides('Atlantic', 0, 'Allied', false);

        static::assertEquals(1, count($result));
        static::assertTrue(in_array('RN', $result, true) || in_array('MN', $result, true));

        $result = $generator->getSides('Atlantic', 0, 'Axis', false);

        static::assertEquals(1, count($result));
        static::assertTrue(in_array('KM', $result, true));
    }

    public function testGetSidesMixedNavies(): void
    {
        $generator = new SideGenerator();
        $result = $generator->getSides('Atlantic', 0, 'Allied', true);

        static::assertEquals(2, count($result));
        static::assertTrue(in_array('RN', $result, true));
        static::assertTrue(in_array('MN', $result, true));
    }

    public function testUnknownSide(): void
    {
        $generator = new SideGenerator();
        static::expectExceptionMessage("Unknown side 'Foo'");
        $generator->getSides('Atlantic', 0, 'Foo', false);
    }
}
