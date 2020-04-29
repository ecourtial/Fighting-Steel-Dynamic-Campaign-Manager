<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Fs;

use App\Core\File\IniReader;

abstract class AbstractShipExtractor
{
    protected IniReader $iniReader;
    protected ?string $currentSide = null;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    /** @return \App\Core\Fs\FsShipInterface[] */
    protected function extractShips(string $filePath, string $lastKey): array
    {
        $ships = [];
        $entryValues = $this->getEmptyValues();

        foreach ($this->iniReader->getData($filePath) as $line) {
            // Check the entry type

            /*
             * Side: only for FS scenario generated by TAS. The one manually created in the TAS scenario folder
             * is often messy and does not always have this field, since it is manually created and is more
             * a technical ship file reference for TAS.
             * Note: The side is only per division, not by ship.
             */
            if ('SIDE' === $line['key'] && 'NIGHTTRAINING' === $lastKey) {
                $this->currentSide = $line['value'];
            }

            if (false === array_key_exists($line['key'], $entryValues)) {
                continue;
            }

            $this->handleLine($entryValues, $ships, $line, $lastKey);
        }

        return $ships;
    }

    /**
     * @param string[]          $entryValues
     * @param FsShipInterface[] $ships
     * @param string[]          $line
     */
    private function handleLine(
        array &$entryValues,
        array &$ships,
        array $line,
        string $lastKey
    ): void {
        // First expected key
        if ('NAME' === $line['key']) {
            $entryValues = static::getEmptyValues();
        }

        $entryValues[$line['key']] = $line['value'];

        // Last expected key
        if ($lastKey === $line['key']) {
            // Check that all the var are filled
            if (false === $this->validateValues($entryValues)) {
                // Another security
                return;
            }

            // Create SHIP here
            $ships[] = $this->createShip($entryValues);

            // clean variables
            $entryValues = $this->getEmptyValues();
        }
    }

    /** @param string[] $entryValues */
    private function validateValues(array &$entryValues): bool
    {
        $allFieldValid = true;
        foreach ($entryValues as $content) {
            if ('' === $content) {
                return false;
            }
        }

        return $allFieldValid;
    }

    /** @return string[] */
    abstract protected function getEmptyValues(): array;

    /** @param string[] $data */
    abstract protected function createShip(array $data): FsShipInterface;
}
