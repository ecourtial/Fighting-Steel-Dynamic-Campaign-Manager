<?php

/**
 * This class represent a Ship entity in the .scn file of Fighting Steel
 *
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Fs\Scenario\Ship;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\FsShipInterface;
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
    protected ?string $side;
    protected ?string $crewQuality;
    protected ?string $crewFatigue;
    protected ?string $nightTraining;

    /** @var string[] */
    public const FIELDS_NAME =
        [
            'NAME',
            'SHORTNAME',
            'TYPE',
            'CLASS',
        ];

    /** @var string[] */
    public const BATTLE_FIELDS = ['CREWQUALITY', 'CREWFATIGUE', 'NIGHTTRAINING'];

    /** @var string[] */
    public const SHIP_TYPES = [
        'BB',
        'BC',
        'CA',
        'CL',
        'DD',
        'TR',
        'CV',
        'CVE',
    ];

    public const LEVEL_GREEN = 'Green';
    public const LEVEL_AVERAGE = 'Average';
    public const LEVEL_VETERAN = 'Veteran';
    public const LEVEL_ELITE = 'Elite';

    public const CREW_FATIGUE_LEVEL = ['Fresh', 'Normal', 'Tired'];
    public const CREW_QUALITY = [self::LEVEL_GREEN, self::LEVEL_AVERAGE, self::LEVEL_VETERAN, self::LEVEL_ELITE];

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

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(string $side): self
    {
        $this->side = $side;

        return $this;
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
        if (false === in_array($type, static::SHIP_TYPES, true)) {
            throw new InvalidInputException("Ship type '{$type}' is unknown");
        }

        $this->type = $type;
    }

    private function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getCrewQuality(): ?string
    {
        return $this->crewQuality;
    }

    public function setCrewQuality(string $crewQuality): self
    {
        if (false === in_array($crewQuality, static::CREW_QUALITY, true)) {
            throw new InvalidInputException("Unknown crew quality level: '{$crewQuality}'");
        }

        $this->crewQuality = $crewQuality;

        return $this;
    }

    public function getCrewFatigue(): ?string
    {
        return $this->crewFatigue;
    }

    public function setCrewFatigue(string $crewFatigue): self
    {
        if (false === in_array($crewFatigue, static::CREW_FATIGUE_LEVEL, true)) {
            throw new InvalidInputException("Unknown fatigue level: '{$crewFatigue}'");
        }

        $this->crewFatigue = $crewFatigue;

        return $this;
    }

    public function getNightTraining(): ?string
    {
        return $this->nightTraining;
    }

    public function setNightTraining(string $nightTraining): self
    {
        $this->nightTraining = $nightTraining;

        return $this;
    }
}
