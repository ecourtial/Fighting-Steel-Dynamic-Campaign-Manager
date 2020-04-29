<?php

/**
 * This class represent a Ship entity in the battle report of Fighting Steel
 *
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Fs\BattleReport;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\FsShipInterface;
use App\Core\Fs\Scenario\Ship\Ship as FsScenarioShip;
use App\Core\Traits\HydrateTrait;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;
use App\NameSwitcher\Exception\InvalidShipDataException;

class Ship implements FsShipInterface
{
    use HydrateTrait;

    protected string $name;
    protected string $shortname;
    protected string $type;
    protected string $class;
    protected string $status;
    protected string $side;

    /** @var string[] */
    public const FIELDS_NAME =
        [
            'NAME',
            'SHORTNAME',
            'TYPE',
            'CLASS',
            'STATUS',
        ];

    public const STATUS = [
      'SHIP_NORMAL',
      'SHIP_SUNK',
    ];

    /** @param string[] $data */
    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortname(): string
    {
        return $this->shortname;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    private function setName(string $name): void
    {
        $this->name = $name;
    }

    private function setShortname(string $shortName): void
    {
        if (strlen($shortName) > DictionaryShip::SHORT_NAME_MAX_LENGTH) {
            throw new InvalidShipDataException("FS Short name is too long: '{$shortName}'");
        }
        $this->shortname = $shortName;
    }

    private function setType(string $type): void
    {
        if (false === in_array($type, FsScenarioShip::SHIP_TYPES, true)) {
            throw new InvalidInputException("Ship type '{$type}' is unknown");
        }

        $this->type = $type;
    }

    private function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    private function setStatus(string $status): void
    {
        if (false === in_array($status, static::STATUS, true)) {
            throw new InvalidInputException("Ship status '{$status}' is unknown");
        }

        $this->status = $status;
    }

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(string $side): void
    {
        $this->side = $side;
    }
}
