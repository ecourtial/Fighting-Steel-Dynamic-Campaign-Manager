<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Ship;

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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setShortname(string $shortName): void
    {
        if (strlen($shortName) > DictionaryShip::SHORT_NAME_MAX_LENGTH) {
            throw new InvalidShipDataException("FS Short name is too long: '{$shortName}'");
        }
        $this->shortname = $shortName;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /** @param string[] $data */
    protected function hydrate(array $data): void
    {
        if (count($data) !== count(static::FIELDS_NAME)) {
            throw new InvalidShipDataException('Invalid ship attribute quantity');
        }

        foreach ($data as $key => $value) {
            if (false === in_array($key, self::FIELDS_NAME, true)) {
                throw new InvalidShipDataException("The attribute '{$key}' is unknown");
            }
            $methodName = 'set' . $key;
            $this->$methodName($value);
        }
    }
}
