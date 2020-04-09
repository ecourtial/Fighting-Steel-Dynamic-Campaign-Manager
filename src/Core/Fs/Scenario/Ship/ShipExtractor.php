<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario\Ship;

use App\Core\Fs\AbstractShipExtractor;

class ShipExtractor extends AbstractShipExtractor
{
    protected bool $requireBattleData;

    /**
     * Note: in to be more accurate, it returns an array of \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan does not understand it.
     *
     * @return \App\Core\Fs\FsShipInterface[]
     */
    public function extract(string $filePath, bool $requireBattleData = false): array
    {
        $lastKey = $requireBattleData ? 'NIGHTTRAINING' : 'CLASS';
        $this->requireBattleData = $requireBattleData;

        return $this->extractShips($filePath, $lastKey, $requireBattleData);
    }

    protected function createShip(array $data): Ship
    {
        $dataCopy = null;
        if ($this->requireBattleData) {
            $dataCopy = $data;
            foreach (Ship::BATTLE_FIELDS as $value) {
                unset($data[$value]);
            }
        }

        $ship = new Ship($data);

        if ($this->requireBattleData) {
            $ship->setSide($this->currentSide);
            $ship->setCrewQuality($dataCopy['CREWQUALITY']);
            $ship->setCrewFatigue($dataCopy['CREWFATIGUE']);
            $ship->setNightTraining($dataCopy['NIGHTTRAINING']);
        }

        return $ship;
    }

    protected function getEmptyValues(): array
    {
        $values = \array_flip(Ship::FIELDS_NAME);

        if ($this->requireBattleData) {
            $values += array_flip(Ship::BATTLE_FIELDS);
        }

        foreach ($values as &$value) {
            $value = '';
        }
        unset($value);

        return $values;
    }
}
