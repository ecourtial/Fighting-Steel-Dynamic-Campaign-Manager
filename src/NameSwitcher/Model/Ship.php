<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Model;

use App\NameSwitcher\Exception\NoShipException;

class Ship
{
    public const SHORT_NAME_MAX_LENGTH = 10;

    protected string $type = '';
    protected string $class = '';
    protected string $tasName = '';
    protected string $fsName = '';
    protected string $fsShortName = '';

    /** @var string[] */
    protected array $similarTo = [];

    /** int qty of fields expected in the dictionary */
    public const FIELD_QTY = 6;

    /** @param string[] $data */
    public function __construct(array $data)
    {
        $this->hydrate($data);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Ship
    {
        $this->type = $type;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): Ship
    {
        $this->class = $class;

        return $this;
    }

    public function getTasName(): string
    {
        return $this->tasName;
    }

    public function setTasName(string $tasName): Ship
    {
        $this->tasName = $tasName;

        return $this;
    }

    public function getFsName(): string
    {
        return $this->fsName;
    }

    public function setFsName(string $fsName): Ship
    {
        $this->fsName = $fsName;

        return $this;
    }

    public function getFsShortName(): string
    {
        return $this->fsShortName;
    }

    public function setFsShortName(string $fsShortName): void
    {
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
                $methodName = 'get' . ucfirst($ruleName);
                if (
                    !method_exists($this, $methodName)
                    || $this->$methodName() !== $ruleValue
                ) {
                    $match = false;
                }
            }

            if (false === $match) {
                break;
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

        return $this->similarTo[random_int(0, $count - 1)];
    }

    /** @param string[] $data */
    protected function hydrate(array $data): Ship
    {
        foreach ($data as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }

        return $this;
    }
}
