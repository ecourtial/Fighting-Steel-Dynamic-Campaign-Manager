<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Model;

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
            throw new NoShipException('No ship found matching the required criteria: ' . $this->formatCriteriaForException($criteria));
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
        $resultCount = count($result);
        $randInt = rand(0, $resultCount - 1);

        return $result[$randInt];
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
                throw new MoreThanOneShipException('More than one result found for the given criteria: ' . $this->formatCriteriaForException($criteria));
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
                $dataToInject[$field] = $element[$field];
            }
            $ship = new Ship($dataToInject);
            $this->dictionary[] = $ship;
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
