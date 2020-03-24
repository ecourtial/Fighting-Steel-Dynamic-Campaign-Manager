<?php

declare(strict_types=1);

/**
 * This class represent a Ship entity in the .scn file of Fighting Steel
 *
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Ship;

use App\Core\Exception\InvalidInputException;
use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Model\Ship as DictionaryShip;

class Ship
{
    protected string $name;
    protected string $shortname;
    protected string $type;
    protected string $class;

    /** @var string[] */
    public const FIELDS_NAME =
        [
            'NAME',
            'SHORTNAME',
            'TYPE',
            'CLASS',
        ];

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
        if (false === in_array($type, static::SHIP_TYPES, true)) {
            throw new InvalidInputException("Ship type '{$type}' is unknown");
        }

        $this->type = $type;
    }

    private function setClass(string $class): void
    {
        $this->class = $class;
    }

    /** @param string[] $data */
    private function hydrate(array $data): void
    {
        if (count($data) !== count(static::FIELDS_NAME)) {
            throw new InvalidShipDataException('Invalid ship attribute quantity');
        }

        foreach ($data as $key => $value) {
            if (false === in_array($key, static::FIELDS_NAME, true)) {
                throw new InvalidShipDataException("The attribute '{$key}' is unknown");
            }

            $methodName = 'set' . $key;
            if (false === method_exists($this, $methodName)) {
                throw new \RuntimeException("Method '{$methodName}' does not exist in " . __CLASS__);
            }

            $this->$methodName($value);
        }
    }
}
