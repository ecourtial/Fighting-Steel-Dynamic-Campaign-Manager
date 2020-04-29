<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\Fleet;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Traits\HydrateTrait;
use App\Core\Traits\SideValidationTrait;
use App\NameSwitcher\Exception\NoShipException;

class Savegame
{
    use HydrateTrait;
    use SideValidationTrait;

    public const FIELDS_NAME = [
        'Fog',
        'ScenarioName',
        'SaveDate',
        'SaveTime',
        'CloudCover',
        'WeatherState',
    ];

    public const PATH_REGEX = '/^Save[1-6 ]*$/';

    private bool $fog;
    private string $scenarioName;
    private int $saveDate;
    private int $saveTime;
    private bool $cloudCover;
    private bool $weatherState;

    private string $path;
    private bool $axisShipsDataChanged = false;
    private bool $alliedShipsDataChanged = false;

    /** @var string[][] */
    private array $axisShipsInPort = [];
    /** @var string[][] */
    private array $alliedShipsInPort = [];
    /** @var Fleet[] */
    private array $axisFleets = [];
    /** @var Fleet[] */
    private array $alliedFleets = [];
    /** @var string[][] */
    private array $shipsData = [];

    protected int $axisMaxTfCount = 0;
    protected int $alliedMaxTfCount = 0;

    /** @param string[] $data */
    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

    public function getFog(): bool
    {
        return $this->fog;
    }

    private function setFog(string $fog): void
    {
        if ('Yes' === $fog) {
            $fog = true;
        } elseif ('No' === $fog) {
            $fog = false;
        } else {
            throw new InvalidInputException("Invalid fog entry: '{$fog}'");
        }

        $this->fog = $fog;
    }

    public function getScenarioName(): string
    {
        return $this->scenarioName;
    }

    private function setScenarioName(string $scenarioName): void
    {
        $this->scenarioName = $scenarioName;
    }

    public function getSaveDate(): int
    {
        return $this->saveDate;
    }

    private function setSaveDate(string $saveDate): void
    {
        $this->saveDate = (int) $saveDate;
    }

    public function getSaveTime(): int
    {
        return $this->saveTime;
    }

    private function setSaveTime(string $saveTime): void
    {
        $this->saveTime = (int) $saveTime;
    }

    public function getCloudCover(): bool
    {
        return $this->cloudCover;
    }

    private function setCloudCover(string $cloudCover): void
    {
        $this->cloudCover = (bool) $cloudCover;
    }

    public function getWeatherState(): bool
    {
        return $this->weatherState;
    }

    private function setWeatherState(string $weatherState): void
    {
        $this->weatherState = (bool) $weatherState;
    }

    /** @param string[][] $ships */
    public function setAxisShipsInPort(array $ships): void
    {
        $this->axisShipsInPort = $ships;
    }

    /** @param Fleet[] $fleets */
    public function setAxisShipsAtSea(array $fleets): void
    {
        $this->updateTfNumber($fleets, Scenario::AXIS_SIDE);

        $this->axisFleets = $fleets;
    }

    /** @param string[][] $ships */
    public function setAlliedShipsInPort(array $ships): void
    {
        $this->alliedShipsInPort = $ships;
    }

    /** @param Fleet[] $fleets */
    public function setAlliedShipsAtSea(array $fleets): void
    {
        $this->updateTfNumber($fleets, Scenario::ALLIED_SIDE);

        $this->alliedFleets = $fleets;
    }

    /** @return  string[][] */
    public function getAxisShipsInPort(): array
    {
        return $this->axisShipsInPort;
    }

    /** @return  Fleet[] */
    public function getAxisFleets(): array
    {
        return $this->axisFleets;
    }

    /** @return  string[][] */
    public function getAlliedShipsInPort(): array
    {
        return $this->alliedShipsInPort;
    }

    /** @return  Fleet[] */
    public function getAlliedFleets(): array
    {
        return $this->alliedFleets;
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

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

    /** @return Fleet[] */
    public function getFleets(string $side): array
    {
        $this->validateSide($side);

        if (Scenario::AXIS_SIDE === $side) {
            return $this->axisFleets;
        } else {
            return $this->alliedFleets;
        }
    }

    public function addFleet(string $side, Fleet $fleet): void
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

    /** @param Fleet[] $fleets */
    private function updateTfNumber(array $fleets, string $side): void
    {
        foreach ($fleets as $fleet) {
            $divNumber = (int) substr($fleet->getId(), 2);
            if (Scenario::AXIS_SIDE === $side) {
                if ($divNumber > $this->axisMaxTfCount) {
                    $this->axisMaxTfCount = $divNumber;
                }
            } else {
                if ($divNumber > $this->alliedMaxTfCount) {
                    $this->alliedMaxTfCount = $divNumber;
                }
            }
        }
    }
}
