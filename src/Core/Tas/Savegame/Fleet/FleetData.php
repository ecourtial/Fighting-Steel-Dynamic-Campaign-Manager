<?php

declare(strict_types=1);

namespace App\Core\Tas\Savegame\Fleet;

class FleetData
{
    /** @var mixed[] */
    private array $divisions = [];
    /** @var string[] */
    private array $ships = [];

    /** @return string[]  */
    public function getShips(): array
    {
        if ([] === $this->ships) {
            foreach ($this->divisions as $divisionName => $data) {
                foreach ($data as $ship => $shipData) {
                    $this->ships[$ship] = $divisionName;
                }
            }
        }

        return $this->ships;
    }

    /** @return string[][][]  */
    public function getDivisions(): array
    {
        return $this->divisions;
    }

    public function removeDivision(string $division): void
    {
        unset($this->divisions[$division]);
    }

    public function removeShipFromDivision(string $division, string $ship): void
    {
        unset($this->divisions[$division][$ship]);
    }

    public function addDataToShipInDivision(string $division, string $ship, string $key, string $value): void
    {
        $this->divisions[$division][$ship][$key] = $value;
    }

    /** @return mixed[] */
    public function getShipDataFromDivision(string $division, string $ship): array
    {
        return $this->divisions[$division][$ship];
    }
}
