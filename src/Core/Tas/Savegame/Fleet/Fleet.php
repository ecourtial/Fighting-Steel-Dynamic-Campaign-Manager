<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;

class Fleet
{
    private string $id;
    private string $name;
    private int $prob;
    private int $caseCount;
    private string $ll;
    /** @var string[] */
    private array $waypoints = [];
    /** @var string[][] */
    private array $divisions = [];
    /** @var string[] */
    private $ships = [];

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setProb(string $prob): void
    {
        $prob = (int) $prob;
        if (100 !== $prob) {
            throw new InvalidInputException('Fleet prob must be 100!');
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

    public function addDivision(string $division): void
    {
        $this->divisions[$division] = [];
    }

    public function addShipToDivision(string $division, string $ship): void
    {
        $this->divisions[$division][] = $ship;
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

    /** @return string[]  */
    public function getDivisions(): array
    {
        return $this->divisions;
    }

    /** @return string[]  */
    public function getShips(): array
    {
        if ([] === $this->ships) {
            foreach ($this->divisions as $divisionName => $ships) {
                foreach ($ships as $ship) {
                    $this->ships[$ship] = $divisionName;
                }
            }
        }

        return $this->ships;
    }
}