<?php

declare(strict_types=1);

namespace App\Core\Tas\Savegame;

use App\Core\Tas\Savegame\Fleet\TaskForce;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Traits\SideValidationTrait;
use App\NameSwitcher\Exception\NoShipException;

class NavalData
{
    use SideValidationTrait;

    private bool $axisShipsDataChanged = false;
    private bool $alliedShipsDataChanged = false;

    /** @var string[][] */
    private array $axisShipsInPort = [];
    /** @var string[][] */
    private array $alliedShipsInPort = [];
    /** @var TaskForce[] */
    private array $axisFleets = [];
    /** @var TaskForce[] */
    private array $alliedFleets = [];
    /** @var string[][] */
    private array $shipsData = [];

    protected int $axisMaxTfCount = 0;
    protected int $alliedMaxTfCount = 0;

    public function isShipsDataChanged(string $side): bool
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            return $this->axisShipsDataChanged;
        } else {
            return $this->alliedShipsDataChanged;
        }
    }

    public function setShipsDataChanged(string $side, bool $dataChanged): void
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            $this->axisShipsDataChanged = $dataChanged;
        } else {
            $this->alliedShipsDataChanged = $dataChanged;
        }
    }

    private function validateShipIsInPort(string $ship, string $side): void
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            if (false === array_key_exists($ship, $this->axisShipsInPort)) {
                throw new NoShipException("Ship '$ship' is not in port on the Axis side");
            }
        } else {
            if (false === array_key_exists($ship, $this->alliedShipsInPort)) {
                throw new NoShipException("Ship '$ship' is not in port on the Allied side");
            }
        }
    }

    public function removeShipInPort(string $ship, string $side): void
    {
        $this->validateShipIsInPort($ship, $side);
        if (Scenario::AXIS_SIDE === $side) {
            unset($this->axisShipsInPort[$ship]);
        } else {
            unset($this->alliedShipsInPort[$ship]);
        }
    }

    public function getMaxTfNumber(string $side): int
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            return $this->axisMaxTfCount;
        } else {
            return $this->alliedMaxTfCount;
        }
    }

    public function incrementMaxTfNumber(string $side): void
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            $this->axisMaxTfCount++;
        } else {
            $this->alliedMaxTfCount++;
        }
    }

    /** @return TaskForce[] */
    public function getFleets(string $side): array
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            return $this->axisFleets;
        } else {
            return $this->alliedFleets;
        }
    }

    public function addFleet(string $side, TaskForce $fleet): void
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            $this->axisFleets[$fleet->getId()] = $fleet;
        } else {
            $this->alliedFleets[$fleet->getId()] = $fleet;
        }
    }

    public function removeFleet(string $side, string $fleetId): void
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            unset($this->axisFleets[$fleetId]);
        } else {
            unset($this->alliedFleets[$fleetId]);
        }
    }

    /** @param string[][] $shipsData */
    public function setShipsData(array $shipsData): void
    {
        $this->shipsData = $shipsData;
    }

    /** @param string[] $shipData */
    public function setShipData(string $ship, array $shipData): void
    {
        $this->shipsData[$ship] = $shipData;
    }

    /** @return string[] */
    public function getShipData(string $ship): array
    {
        if (false === array_key_exists($ship, $this->shipsData)) {
            throw new NoShipException("Ship '$ship' not found in the savegame");
        }

        return $this->shipsData[$ship];
    }

    /** @param TaskForce[] $fleets */
    public function setShipsAtSea(string $side, array $fleets): void
    {
        $this->validateSide($side);
        if (Scenario::AXIS_SIDE === $side) {
            $this->axisFleets = $fleets;
        } else {
            $this->alliedFleets = $fleets;
        }

        foreach ($fleets as $fleet) {
            $divNumber = (int) substr($fleet->getId(), 2);
            if (Scenario::AXIS_SIDE === $side) {
                if ($divNumber !== $this->axisMaxTfCount) {
                    $this->axisMaxTfCount = $divNumber;
                }
            } else {
                if ($divNumber !== $this->alliedMaxTfCount) {
                    $this->alliedMaxTfCount = $divNumber;
                }
            }
        }
    }

    /** @return  string[][] */
    public function getShipsInPort(string $side): array
    {
        $this->validateSide($side);
        if (Scenario::AXIS_SIDE === $side) {
            return $this->axisShipsInPort;
        } else {
            return $this->alliedShipsInPort;
        }
    }

    /** @param  string[][] $ships */
    public function setShipsInPort(string $side, array $ships): void
    {
        $this->validateSide($side);
        if (Scenario::AXIS_SIDE === $side) {
            $this->axisShipsInPort = $ships;
        } else {
            $this->alliedShipsInPort = $ships;
        }
    }
}
