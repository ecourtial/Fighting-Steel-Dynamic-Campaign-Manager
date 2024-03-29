<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Fs\Scenario\Ship;

use App\Core\Fs\AbstractShipExtractor;

class ShipExtractor extends AbstractShipExtractor
{
    protected bool $requireBattleData;

    /**
     * Note: in to be more accurate, it returns an array of \App\Core\Fs\Scenario\Ship\Ship
     * but PHPStan does not understand it.
     *
     * Note II: this method is used to read the content of a FS .scn file. The one generated by TAS and the
     * one created by the user for TAS containing all the data of all the ships of the scenario.
     *
     * @return \App\Core\Fs\FsShipInterface[]
     */
    public function extract(string $filePath, string $lastKey): array
    {
        $this->requireBattleData = Ship::LAST_BATTLE_FIELD_KEY === $lastKey;

        return $this->extractShips($filePath, $lastKey);
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
            $ship->setRadarLevel($dataCopy['RADARTYPE']);
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
