<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Ship;

use App\Core\File\IniReader;
use App\Core\Tas\Scenario\Scenario;

class ShipExtractor
{
    protected const DATA_MAPPING = [
        'NAME' => '',
        'SHORTNAME' => '',
        'TYPE' => '',
        'CLASS' => '',
    ];

    protected IniReader $iniReader;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    /** @return Ship[] */
    public function extract(Scenario $scenario): array
    {
        $filePath = $scenario->getFullPath() . DIRECTORY_SEPARATOR . 'GR.scn';
        $ships = [];
        $entryValues = static::DATA_MAPPING;

        foreach ($this->iniReader->getData($filePath) as $line) {
            // Check the entry type
            if (false === array_key_exists($line['key'], $entryValues)) {
                continue;
            }

            $this->handleLine($entryValues, $ships, $line);
        }

        return $ships;
    }

    /**
     * @param string[] $entryValues
     * @param Ship[]   $ships
     * @param string[] $line
     */
    private function handleLine(
        array &$entryValues,
        array &$ships,
        array $line
    ): void {
        // First expected key
        if ('NAME' === $line['key']) {
            $entryValues = static::DATA_MAPPING;
        }

        $entryValues[$line['key']] = $line['value'];

        // Last expected key
        if ('CLASS' === $line['key']) {
            // Check that all the var are filled
            if (false === $this->validateValues($entryValues)) {
                // Another security
                return;
            }

            // Create SHIP here
            $ships[] = new Ship($entryValues);

            // clean variables
            $entryValues = static::DATA_MAPPING;
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
}
