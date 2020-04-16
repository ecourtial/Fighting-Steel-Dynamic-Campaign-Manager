<?php

declare(strict_types=1);

namespace App\NameSwitcher\Switcher;

use App\NameSwitcher\Dictionary\Dictionary;

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */
interface SwitcherInterface
{
    public const SWITCH_BASIC = 'switch_basic';
    public const SWITCH_CLASS = 'switch_class';
    public const SWITCH_WITH_ERROR = 'switch_error';

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $fsShips
     *
     * @return \App\NameSwitcher\Transformer\Ship[]
     */
    public function switch(Dictionary $dictionary, array $fsShips, string $playerSide): array;
}
