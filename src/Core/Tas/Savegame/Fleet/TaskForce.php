<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;

class TaskForce
{
    public const MISSION_TYPES = [
        'Breakthrough',
        'Cover',
        'Convoy',
        'Intercept',
        'TacTransport',
        'Bombard',
        'SlTacTransport',
        'InterceptTR',
        'Patrol',
        'HvyTacTransport',
        'Minelaying',
        'Replenish',
    ];

    private string $id;
    private string $name;
    private int $prob;
    private int $caseCount;
    private string $ll;
    private float $speed;
    private string $mission;
    /** @var string[] */
    private array $waypoints = [];
    private FleetData $fleetData;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->fleetData = new FleetData();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setProb(string $prob): void
    {
        $prob = (int) $prob;
        if ($prob < 1 || $prob > 100) {
            throw new InvalidInputException("Fleet prob must be >= 1 and <= 100! $prob given.");
        }
        $this->prob = $prob;
    }

    public function setCaseCount(string $count): void
    {
        $count = (int) $count;
        if (1 !== $count) {
            throw new InvalidInputException('Fleet case count must be 1!');
        }
        $this->caseCount = $count;
    }

    public function setLl(string $ll): void
    {
        $this->ll = $ll;
    }

    public function addWaypoint(string $wp): void
    {
        $this->waypoints[] = $wp;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProb(): int
    {
        return $this->prob;
    }

    public function getCaseCount(): int
    {
        return $this->caseCount;
    }

    public function getLl(): string
    {
        return $this->ll;
    }

    /** @return string[]  */
    public function getWaypoints(): array
    {
        return $this->waypoints;
    }

    public function getSpeed(): float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): void
    {
        $this->speed = $speed;
    }

    public function getMission(): string
    {
        return $this->mission;
    }

    public function setMission(string $mission): void
    {
        if (false === in_array($mission, static::MISSION_TYPES, true)) {
            throw new InvalidInputException("Unknown mission type: '$mission'");
        }

        $this->mission = $mission;
    }

    public function getFleetData(): FleetData
    {
        return $this->fleetData;
    }
}
