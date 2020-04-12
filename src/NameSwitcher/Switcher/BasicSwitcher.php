<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

use App\Core\Tas\Scenario\Scenario;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Transformer\Ship;

class BasicSwitcher implements SwitcherInterface
{
    /** @return \App\NameSwitcher\Transformer\Ship[] */
    public function switch(Dictionary $dictionary, Scenario $scenario, string $playerSide): array
    {
        $correspondence = [];
        foreach ($scenario->getFsShips() as $fsShip) {
            // Reminder. At this point, the Name in the FS file is the same as in TAS.
            $correspondence[] = new Ship(
                $fsShip->getName(),
                $dictionary->getShipsList()[$fsShip->getName()]->getFsName(),
                $dictionary->getShipsList()[$fsShip->getName()]->getFsShortName()
            );
        }

        return $correspondence;
    }
}
