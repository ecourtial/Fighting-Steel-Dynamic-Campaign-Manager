<?php

declare(strict_types=1);

namespace App\NameSwitcher\Switcher;

use App\Core\Exception\InvalidInputException;

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */
class SwitcherFactory
{
    public function getSwitcher(string $type): SwitcherInterface
    {
        switch ($type) {
            case SwitcherInterface::SWITCH_BASIC:
                return new BasicSwitcher();
            break;
            default:
                throw new InvalidInputException("Unknown switcher type: '{$type}'");
        }
    }
}
