<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

use App\Core\Exception\InvalidInputException;

class SideDetector
{
    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $fsShips
     */
    public function detectSide(array $fsShips, string $oneShip): string
    {
        foreach ($fsShips as $fsShip) {
            /** @var \App\Core\Fs\Scenario\Ship\Ship $fsShip */
            if ($fsShip->getName() === $oneShip) {
                return $fsShip->getSide();
            }
        }

        throw new InvalidInputException("Side detector error. The ship '{$oneShip}' is missing");
    }
}
