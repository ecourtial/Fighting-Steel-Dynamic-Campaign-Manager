<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\Fleet;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Traits\HydrateTrait;
use App\NameSwitcher\Exception\NoShipException;

class Savegame
{
    use HydrateTrait;

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

    /** @var string[] */
    private array $axisShipsInPort = [];
    /** @var string[] */
    private array $alliedShipsInPort = [];
    /** @var Fleet[] */
    private array $axisFleets = [];
    /** @var Fleet[] */
    private array $alliedFleets = [];
    /** @var string[] */
    private array $shipsData = [];

    /** @param string[] $data */
    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

    public function getFog(): bool
    {
        return $this->fog;
    }

    public function setFog(string $fog): void
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

    public function setScenarioName(string $scenarioName): void
    {
        $this->scenarioName = $scenarioName;
    }

    public function getSaveDate(): int
    {
        return $this->saveDate;
    }

    public function setSaveDate(string $saveDate): void
    {
        $this->saveDate = (int) $saveDate;
    }

    public function getSaveTime(): int
    {
        return $this->saveTime;
    }

    public function setSaveTime(string $saveTime): void
    {
        $this->saveTime = (int) $saveTime;
    }

    public function getCloudCover(): bool
    {
        return $this->cloudCover;
    }

    public function setCloudCover(string $cloudCover): void
    {
        $this->cloudCover = (bool) $cloudCover;
    }

    public function getWeatherState(): bool
    {
        return $this->weatherState;
    }

    public function setWeatherState(string $weatherState): void
    {
        $this->weatherState = (bool) $weatherState;
    }

    /** @param string[] $ships */
    public function setAxisShipsInPort(array $ships): void
    {
        $this->axisShipsInPort = $ships;
    }

    /** @param Fleet[] $fleets */
    public function setAxisShipsAtSea(array $fleets): void
    {
        $this->axisFleets = $fleets;
    }

    /** @param string[] $ships */
    public function setAlliedShipsInPort(array $ships): void
    {
        $this->alliedShipsInPort = $ships;
    }

    /** @param Fleet[] $fleets */
    public function setAlliedShipsAtSea(array $fleets): void
    {
        $this->alliedFleets = $fleets;
    }

    /** @return  string[] */
    public function getAxisShipsInPort(): array
    {
        return $this->axisShipsInPort;
    }

    /** @return  Fleet[] */
    public function getAxisFleets(): array
    {
        return $this->axisFleets;
    }

    /** @return  string[] */
    public function getAlliedShipsInPort(): array
    {
        return $this->alliedShipsInPort;
    }

    /** @return  Fleet[] */
    public function getAlliedFleets(): array
    {
        return $this->alliedFleets;
    }

    /** @param string[] $shipsData */
    public function setShipsData(array $shipsData): void
    {
        $this->shipsData = $shipsData;
    }

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
        if ($side === Scenario::AXIS_SIDE) {
            return $this->axisShipsDataChanged;
        } elseif ($side === Scenario::ALLIED_SIDE) {
            return $this->alliedShipsDataChanged;
        } else {
            throw new InvalidInputException("Side '$side' is unknown");
        }
    }

    public function setShipsDataChanged(string $side, bool $dataChanged): void
    {
        if ($side === Scenario::AXIS_SIDE) {
            $this->axisShipsDataChanged = $dataChanged;
        } elseif ($side === Scenario::ALLIED_SIDE) {
            $this->alliedShipsDataChanged = $dataChanged;
        } else {
            throw new InvalidInputException("Side '$side' is unknown");
        }
    }

    private function validateShipIsInPort(string $ship, string $side): void
    {
        if ($side === Scenario::AXIS_SIDE) {
            if (false === array_key_exists($ship, $this->axisShipsInPort)) {
                throw new InvalidInputException("Ship '$ship' is not in port on the axis side");
            }
        } elseif ($side === Scenario::ALLIED_SIDE) {
            if (false === array_key_exists($ship, $this->alliedShipsInPort)) {
                throw new InvalidInputException("Ship '$ship' is not in port on the allied side");
            }
        } else {
            throw new InvalidInputException("Side '$side' is unknown");
        }
    }

    public function removeShipInPort(string $ship, string $side): void
    {
        $this->validateShipIsInPort($ship, $side);
        if ($side === Scenario::AXIS_SIDE) {
            unset($this->axisShipsInPort[$ship]);
        } else {
            unset($this->alliedShipsInPort[$ship]);
        }
    }
}
