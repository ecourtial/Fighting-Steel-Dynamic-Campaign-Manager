<?php

declare(strict_types=1);
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\Scenario\Ship\ShipExtractor;

class SideDetector
{
    private ShipExtractor $shipExtractor;

    public function __construct(ShipExtractor $shipExtractor)
    {
        $this->shipExtractor = $shipExtractor;
    }

    public function detectSide(string $path, string $oneShip): string
    {
        $ships = $this->shipExtractor->extract($path, true);
        foreach ($ships as $fsShip) {
            /** @var \App\Core\Fs\Scenario\Ship\Ship $fsShip */
            if ($fsShip->getName() === $oneShip) {
                return $fsShip->getSide();
            }
        }

        throw new InvalidInputException("Side detector error. The ship '{$oneShip}' is missing");
    }
}
