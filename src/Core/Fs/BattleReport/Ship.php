<?php

declare(strict_types=1);

/**
 * This class represent a Ship entity in the battle report of Fighting Steel
 *
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\BattleReport;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\FsShipInterface;
use App\Core\Fs\Scenario\Ship\Ship as FsScenarioShip;
use App\Core\Traits\HydrateTrait;
use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Model\Ship as DictionaryShip;

class Ship implements FsShipInterface
{
    use HydrateTrait;

    protected string $name;
    protected string $shortname;
    protected string $type;
    protected string $class;
    protected string $status;

    /** @var string[] */
    public const FIELDS_NAME =
        [
            'NAME',
            'SHORTNAME',
            'TYPE',
            'CLASS',
            'STATUS',
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
        $this->status = $status;
    }
}
