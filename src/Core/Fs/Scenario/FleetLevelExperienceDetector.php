<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

class FleetLevelExperienceDetector
{
    public const GREEN_COEF = 6;
    public const AVERAGE_COEF = 4;
    public const VETERAN_COEF = 2;

    public const LEVEL_GREEN = 'Green';
    public const LEVEL_AVERAGE = 'Average';
    public const LEVEL_VETERAN = 'Veteran';
    public const LEVEL_ELITE = 'Elite';

    /** @param \App\Core\Fs\Scenario\Ship\Ship[] $fsShips */
    public function getFleetLevel(array $fsShips, string $side): string
    {
        $experience = [
            static::LEVEL_GREEN => 0,
            static::LEVEL_AVERAGE => 0,
            static::LEVEL_VETERAN => 0,
            static::LEVEL_ELITE => 0,
        ];

        $shipCount = 0;

        foreach ($fsShips as $ship) {
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
        $experiences[static::LEVEL_GREEN] = $experiences[static::LEVEL_GREEN] * static::GREEN_COEF;
        $experiences[static::LEVEL_AVERAGE] = $experiences[static::LEVEL_AVERAGE] * static::AVERAGE_COEF;
        $experiences[static::LEVEL_VETERAN] = $experiences[static::LEVEL_VETERAN] * static::VETERAN_COEF;

        $sum = 0;
        foreach ($experiences as $experience) {
            $sum += $experience;
        }
        $moy = $sum / $shipCount;

        if ($moy < 2) {
            $level = static::LEVEL_ELITE;
        } elseif ($moy <= 3) {
            $level = static::LEVEL_VETERAN;
        } elseif ($moy < 5) {
            $level = static::LEVEL_AVERAGE;
        } else {
            $level = static::LEVEL_GREEN;
        }

        return $level;
    }
}
