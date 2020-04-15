<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

use App\Core\Fs\Scenario\Ship\Ship;

class FleetLevelExperienceDetector
{
    public const GREEN_COEF = 6;
    public const AVERAGE_COEF = 4;
    public const VETERAN_COEF = 2;

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $scenarioShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $fsShips
     */
    public function getFleetLevel(array $fsShips, string $side): string
    {
        $experience = [
            Ship::LEVEL_GREEN => 0,
            Ship::LEVEL_AVERAGE => 0,
            Ship::LEVEL_VETERAN => 0,
            Ship::LEVEL_ELITE => 0,
        ];

        $shipCount = 0;

        foreach ($fsShips as $ship) {
            /** @var \App\Core\Fs\Scenario\Ship\Ship $ship */
            if ($side === $ship->getSide()) {
                $experience[$ship->getCrewQuality()]++;
                $shipCount++;
            }
        }

        return $this->evaluateLevel($experience, $shipCount);
    }

    /** @param int[] $experiences */
    private function evaluateLevel(array $experiences, int $shipCount): string
    {
        $experiences[Ship::LEVEL_GREEN] = $experiences[Ship::LEVEL_GREEN] * static::GREEN_COEF;
        $experiences[Ship::LEVEL_AVERAGE] = $experiences[Ship::LEVEL_AVERAGE] * static::AVERAGE_COEF;
        $experiences[Ship::LEVEL_VETERAN] = $experiences[Ship::LEVEL_VETERAN] * static::VETERAN_COEF;

        $sum = 0;
        foreach ($experiences as $experience) {
            $sum += $experience;
        }
        $moy = $sum / $shipCount;

        if ($moy < 2) {
            $level = Ship::LEVEL_ELITE;
        } elseif ($moy <= 3) {
            $level = Ship::LEVEL_VETERAN;
        } elseif ($moy < 5) {
            $level = Ship::LEVEL_AVERAGE;
        } else {
            $level = Ship::LEVEL_GREEN;
        }

        return $level;
    }
}
