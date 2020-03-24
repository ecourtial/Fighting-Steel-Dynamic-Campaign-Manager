<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Model;

use App\Core\Exception\InvalidInputException;
use App\Core\Fs\Ship\Ship as FsShip;
use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Exception\NoShipException;

class Ship
{
    public const SHORT_NAME_MAX_LENGTH = 10;

    /** @var string[] */
    public const FIELDS_NAME =
        [
            'Type',
            'Class',
            'TasName',
            'FsClass',
            'FsName',
            'FsShortName',
            'SimilarTo',
        ];

    protected string $type = '';
    protected string $class = '';
    protected string $tasName = '';
    protected string $fsClass = '';
    protected string $fsName = '';
    protected string $fsShortName = '';

    /** @var string[] */
    protected array $similarTo = [];

    /** @param string[] $data */
    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

    public function getType(): string
    {
        return $this->type;
    }

    private function setType(string $type): Ship
    {
        if (false === in_array($type, FsShip::SHIP_TYPES, true)) {
            throw new InvalidInputException("Ship type '{$type}' is unknown");
        }
        $this->type = $type;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    private function setClass(string $class): Ship
    {
        $this->class = $class;

        return $this;
    }

    public function getTasName(): string
    {
        return $this->tasName;
    }

    private function setTasName(string $tasName): Ship
    {
        $this->tasName = $tasName;

        return $this;
    }

    public function getFsClass(): string
    {
        return $this->fsClass;
    }

    private function setFsClass(string $fsClass): Ship
    {
        $this->fsClass = $fsClass;

        return $this;
    }

    public function getFsName(): string
    {
        return $this->fsName;
    }

    private function setFsName(string $fsName): Ship
    {
        $this->fsName = $fsName;

        return $this;
    }

    public function getFsShortName(): string
    {
        return $this->fsShortName;
    }

    private function setFsShortName(string $fsShortName): void
    {
        if (strlen($fsShortName) > static::SHORT_NAME_MAX_LENGTH) {
            throw new InvalidShipDataException("FS Short name is too long: '{$fsShortName}'");
        }
        $this->fsShortName = $fsShortName;
    }

    /** @return string[] */
    public function getSimilarTo(): array
    {
        return $this->similarTo;
    }

    public function setSimilarTo(?string $similarTo): Ship
    {
        if (is_null($similarTo)) {
            $this->similarTo = [];

            return $this;
        }

        $similarShips = explode(
            '|',
            $similarTo
        );

        $ships = [];
        foreach ($similarShips as $shipString) {
            $ships[] = $shipString;
        }
        $this->similarTo = $ships;

        return $this;
    }

    /** @param string[] $criteria */
    public function matchCriteria(array $criteria): bool
    {
        $match = true;

        // Rule: if one rule is not matched : rejected
        foreach ($criteria as $ruleName => $ruleValue) {
            if ('SimilarTo' === $ruleName) {
                if (false === in_array($ruleValue, $this->similarTo, true)) {
                    $match = false;
                }
            } else {
                $methodName = 'get' . $ruleName;
                if (
                    !method_exists($this, $methodName)
                    || $this->$methodName() !== $ruleValue
                ) {
                    $match = false;
                }
            }

            if (false === $match) {
                return $match;
            }
        }

        return $match;
    }

    public function getRandomSimilarShip(): string
    {
        $count = count($this->similarTo);
        if (0 === $count) {
            throw new NoShipException('No similar ship found for ' . $this->getType() . ' ' . $this->getTasName());
        }

        return $this->similarTo[array_rand($this->similarTo)];
    }

    /** @param string[] $data */
    private function hydrate(array $data): Ship
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

        return $this;
    }
}
