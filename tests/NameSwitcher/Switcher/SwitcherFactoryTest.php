<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       08/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\NameSwitcher\Switcher;

use App\Core\Exception\InvalidInputException;
use App\NameSwitcher\Switcher\BasicSwitcher;
use App\NameSwitcher\Switcher\SwitcherFactory;
use App\NameSwitcher\Switcher\SwitcherInterface;
use PHPUnit\Framework\TestCase;

class SwitcherFactoryTest extends TestCase
{
    public function testBasicSwitcher(): void
    {
        $factory = new SwitcherFactory();
        $switcher = $factory->getSwitcher(SwitcherInterface::SWITCH_BASIC);
        static::assertEquals(BasicSwitcher::class, get_class($switcher));
    }

    public function testUnknownSwitcher(): void
    {
        $factory = new SwitcherFactory();
        try {
            $factory->getSwitcher('AH');
            static::fail('Since the switcher type is invalid, and exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Unknown switcher type: 'AH'",
                $exception->getMessage()
            );
        }
    }
}
