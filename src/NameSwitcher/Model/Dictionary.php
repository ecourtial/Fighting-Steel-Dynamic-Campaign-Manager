<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Model;

use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Exception\MoreThanOneShipException;
use App\NameSwitcher\Exception\NoShipException;

class Dictionary
{
    /** @var Ship[] */
    protected array $dictionary = [];

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

    /** @param string[][] $readRawData */
    public function __construct(array $readRawData)
    {
        $this->hydrate($readRawData);
    }

    /** @return Ship[] */
    public function getShipsList(): array
    {
        return $this->dictionary;
    }

    /**
     * @param string[] $criteria
     *
     * @return Ship[]
     *
     * @throws NoShipException
     */
    public function searchInList(array $criteria): array
    {
        $result = [];

        foreach ($this->dictionary as $ship) {
            if ($ship->matchCriteria($criteria)) {
                $result[] = $ship;
            }
        }

        if (0 === count($result)) {
            $errorMsg = 'No ship found matching the required criteria: '
                . $this->formatCriteriaForException($criteria);
            throw new NoShipException($errorMsg);
        }

        return $result;
    }

    /**
     * @param string[] $criteria
     *
     * @throws NoShipException
     * @throws \Exception
     */
    public function randomWithCriteria(array $criteria): Ship
    {
        $result = $this->searchInList($criteria);

        return $result[array_rand($result)];
    }

    /**
     * @param string[] $criteria
     *
     * @throws MoreThanOneShipException
     * @throws NoShipException
     * @throws \Exception
     */
    public function findOneShip(array $criteria, bool $random = false): Ship
    {
        if ($random) {
            $ship = $this->randomWithCriteria($criteria);
        } else {
            $result = $this->searchInList($criteria);
            if (count($result) > 1) {
                $errorMsg = 'More than one result found for the given criteria: '
                    . $this->formatCriteriaForException($criteria);
                throw new MoreThanOneShipException($errorMsg);
            }
            $ship = $result[0];
        }

        return $ship;
    }

    /** @param string[][] $data */
    protected function hydrate(array $data): void
    {
        foreach ($data as $element) {
            $dataToInject = [];
            foreach (self::FIELDS_NAME as $field) {
                if (false === array_key_exists($field, $element)) {
                    $errorMsg = "Field '{$field}' is missing. Given data was: "
                        . $this->formatCriteriaForException($element);
                    throw new InvalidShipDataException($errorMsg);
                }
                $dataToInject[$field] = $element[$field];
            }
            $ship = new Ship($dataToInject);
            $this->dictionary[$ship->getTasName()] = $ship;
        }
    }

    /** @param string[] $criteria */
    protected function formatCriteriaForException(array $criteria): string
    {
        $msg = [];
        foreach ($criteria as $key => $value) {
            $msg[] = "'{$key}' => '$value'";
        }

        return implode(',', $msg);
    }
}
