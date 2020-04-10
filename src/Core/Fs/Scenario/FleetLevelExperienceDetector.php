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

    /** @param int[] */
    private function evaluateLevel(array $experiences, int $shipCount): string
    {
        $experiences['Green'] = $experiences['Green'] * 6;
        $experiences['Average'] = $experiences['Average'] * 4;
        $experiences['Veteran'] = $experiences['Veteran'] * 2;
        $experiences['Elite'] = $experiences['Elite'] * 1;

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
