<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;
use App\Core\Traits\HydrateTrait;

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

    public function getShipLocation(string $ship): string
    {
    }
}
