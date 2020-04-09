<?php

declare(strict_types=1);

namespace App\NameSwitcher\Switcher;

use App\Core\Tas\Scenario\Scenario;
use App\NameSwitcher\Dictionary\Dictionary;

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */
interface SwitcherInterface
{
    public const SWITCH_BASIC = 'switch_basic';
    public const SWITCH_CLASS = 'switch_class';
    public const SWITCH_WITH_ERROR = 'switch_basic';

    /** @return \App\NameSwitcher\Transformer\Ship[] */
    public function switch(Dictionary $dictionary, Scenario $scenario, string $playerSide): array;
}
