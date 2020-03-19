<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\Exception\SideErrorException;
use App\Core\Fs\Ship\Ship as FsShip;
use App\Core\Tas\Exception\DuplicateShipException;
use App\Core\Tas\Ship\Ship as TasShip;

class Scenario
{
    public const ALLIED_SIDE = 'Allied';
    public const AXIS_SIDE = 'Axis';

    public const SIDES = [
        self::ALLIED_SIDE,
        self::AXIS_SIDE,
    ];

    protected string $name;
    protected string $fullPath;
    protected string $shipDataFile;

    /** @var TasShip[] */
    protected array $alliedShips = [];

    /** @var TasShip[] */
    protected array $axisShips = [];

    /** @var FsShip[] */
    protected $fsShips = [];

    public function __construct(string $name, string $fullPath, string $shipDataFile)
    {
        $this->name = $name;
        $this->fullPath = $fullPath;
        $this->shipDataFile = $shipDataFile;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function getShipDataFile(): string
    {
        return $this->shipDataFile;
    }

    /**
     * In fact $ships is an array of TasShip.
     * But PHPStan is complaining because of my double check (instance of).
     * I keep it because we have no guarantee that the dev will pass us
     * an array of Ship objects.
     *
     * @param mixed[] $ships
     */
    public function setTasShips(string $side, array $ships): void
    {
        static::validateSide($side);
        $propertyName = strtolower($side) . 'Ships';
        $this->$propertyName = [];
        $count = 0;

        foreach ($ships as $ship) {
            if (false === $ship instanceof TasShip) {
                throw new InvalidInputException("Data at index #{$count} is not a proper TAS Ship object");
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

    /** @return TasShip[] */
    public function getTasShips(string $side): array
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

    /** @return FsShip[] */
    public function getFsShips(): array
    {
        return $this->fsShips;
    }

    /**
     * In fact $ships is an array of FsShip.
     * But PHPStan is complaining because of my double check (instance of).
     * I keep it because we have no guarantee that the dev will pass us
     * an array of Ship objects.
     *
     * @param mixed[] $ships
     */
    public function setFsShips(array $ships): void
    {
        $count = 0;
        $this->fsShips = [];

        foreach ($ships as $ship) {
            if (false === $ship instanceof FsShip) {
                throw new InvalidInputException("Data at index #{$count} is not a proper FS Ship object");
            }

            /** @var \App\Core\Fs\Ship\Ship $ship */
            if (array_key_exists($ship->getName(), $this->fsShips)) {
                throw new DuplicateShipException($ship->getName());
            }

            $this->fsShips[$ship->getName()] = $ship;
            $count++;
        }
    }
}
