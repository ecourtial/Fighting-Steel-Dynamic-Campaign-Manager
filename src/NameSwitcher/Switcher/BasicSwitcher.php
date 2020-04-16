<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

use App\Core\Fs\FsShipInterface;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Transformer\Ship;

class BasicSwitcher implements SwitcherInterface
{
    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $fsShips
     *
     * @return \App\NameSwitcher\Transformer\Ship[]
     */
    public function switch(Dictionary $dictionary, array $fsShips, string $playerSide): array
    {
        // In the basic switcher, we switch everything: we apply the dictionary without any special rule.
        $correspondence = [];
        foreach ($fsShips as $fsShip) {
            // Reminder. At this point, the Name in the FS file is the same as in TAS.
            $correspondence[$fsShip->getName()] = $this->createBasicSwitch($dictionary, $fsShip);
        }

        return $correspondence;
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan has issue with interpreting interfaces
     */
    protected function createBasicSwitch(Dictionary $dictionary, FsShipInterface $fsShip): Ship
    {
        return new Ship(
            $fsShip->getName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getFsName(),
            $dictionary->getShipsList()[$fsShip->getName()]->getFsShortName()
        );
    }
}
