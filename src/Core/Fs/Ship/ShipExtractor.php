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

        $entryValues = [
            'NAME' => '',
            'SHORTNAME' => '',
            'TYPE' => '',
            'CLASS' => '',
        ];

        // Variable for parsing the current entry
        $mayBe = false;

        foreach ($this->iniReader->getData($filePath) as $line) {
            // Check the entry type
            if (false === array_key_exists($line['key'], $entryValues)) {
                continue;
            }

            $this->handleLine($entryValues, $ships, $line, $mayBe);
        }

        return $ships;
    }

    /**
     * @param string[] $entryValues
     * @param Ship[] $ships
     * @param string[] $line
     * @param bool  $mayBe
     */
    protected function handleLine(
        array &$entryValues,
        array &$ships,
        array $line,
        bool &$mayBe): void
    {
        // Safety to ignore non matching entries (good expected order)
        if (false === $mayBe && 'NAME' !== $line['key']) {
            $this->cleanVariables($entryValues);

            return;
        }

        // First expected key
        if ('NAME' === $line['key']) {
            $mayBe = true;
        }

        $entryValues[$line['key']] = $line['value'];

        // Last expected key
        if ($mayBe && 'CLASS' === $line['key']) {
            // Check that all the var are filled
            if (false === $this->validateValues($entryValues)) {
                return; // Another security
            }

            $mayBe = false;

            // Create SHIP here
            $ships[] = new Ship($entryValues);

            // clean variables
            $this->cleanVariables($entryValues);
        }
    }

    /** @param string[] $entryValues */
    protected function validateValues(array &$entryValues): bool
    {
        $allFieldValid = true;
        foreach ($entryValues as $content) {
            if ('' === $content) {
                $this->cleanVariables($entryValues);
                $allFieldValid = false;
                break;
            }
        }

        return $allFieldValid;
    }

    /** @param string[] $entryValues */
    protected function cleanVariables(array &$entryValues): void
    {
        foreach ($entryValues as &$varContent) {
            $varContent = '';
        }
        unset($varContent);
    }
}
