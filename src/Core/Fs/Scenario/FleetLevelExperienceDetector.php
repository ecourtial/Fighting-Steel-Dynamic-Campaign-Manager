<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

use App\Core\Tas\Scenario\Scenario;

class FleetLevelExperienceDetector
{
    public const GREEN_COEF = 6;
    public const AVERAGE_COEF = 4;
    public const VETERAN_COEF = 2;
    public const ELITE_COEF = 1;

    public function getFleetLevel(Scenario $scenario, string $side): string
    {
        $experience = [
            'Green' => 0,
            'Average' => 0,
            'Veteran' => 0,
            'Elite' => 0,
        ];

        $shipCount = 0;

        foreach ($scenario->getFsShips() as $ship) {
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
        $experiences['Green'] = $experiences['Green'] * static::GREEN_COEF;
        $experiences['Average'] = $experiences['Average'] * static::AVERAGE_COEF;
        $experiences['Veteran'] = $experiences['Veteran'] * static::VETERAN_COEF;
        $experiences['Elite'] = $experiences['Elite'] * static::ELITE_COEF;

        $sum = 0;
        foreach ($experiences as $experience) {
            $sum += $experience;
        }
        $moy = $sum / $shipCount;

        if ($moy < 2) {
            return 'Elite';
        } elseif ($moy < 3) {
            return 'Veteran';
        } elseif ($moy < 5) {
            return 'Average';
        } else {
            return 'Green';
        }
    }
}
