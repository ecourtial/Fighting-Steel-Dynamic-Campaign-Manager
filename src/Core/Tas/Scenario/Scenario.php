<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\Exception\SideErrorException;
use App\Core\Tas\Exception\DuplicateShipException;
use App\Core\Tas\Ship\Ship;

class Scenario
{
    public const ALLIED_SIDE = 'Allied';
    public const AXIS_SIDE = 'Axis';

    public const SIDES = [
        self::ALLIED_SIDE,
        self::AXIS_SIDE,
    ];

    private string $name;
    private string $fullPath;

    /** @var Ship[] */
    private array $alliedShips = [];

    /** @var Ship[] */
    private array $axisShips = [];

    public function __construct(string $name, string $fullPath)
    {
        $this->name = $name;
        $this->fullPath = $fullPath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /** @param Ship[] */
    public function setShips(string $side, array $ships): void
    {
        static::validateSide($side);
        $propertyName = strtolower($side) . 'Ships';
        $this->$propertyName = [];
        $count = 0;

        foreach ($ships as $ship) {
            if (false === $ship instanceof Ship) {
                throw new InvalidInputException("Data at index #{$count} is not a proper Ship object");
            }

            /** @var \App\Core\Tas\Ship\Ship $ship */
            $existInSide = '';
            if (array_key_exists($ship->getName(), $this->alliedShips)) {
                $existInSide = 'Allied';
            } elseif (array_key_exists($ship->getName(), $this->axisShips)) {
                $existInSide = 'Axis';
            }

            if ('' !== $existInSide) {
                throw new DuplicateShipException($ship->getName(), $existInSide);
            }

            $this->$propertyName[$ship->getName()] = $ship;
            $count++;
        }
    }

    /** @return Ship[] */
    public function getShips(string $side): array
    {
        static::validateSide($side);
        $propertyName = strtolower($side) . 'Ships';

        return $this->$propertyName;
    }

    public static function validateSide(string $side): void
    {
        if (false === in_array($side, static::SIDES, true)) {
            throw new SideErrorException("Invalid side: '{$side}'");
        }
    }
}
