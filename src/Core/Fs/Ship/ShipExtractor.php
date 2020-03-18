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

        // Variable for parsing the current entry
        $mayBe = false;

        $entryValues = [
            'NAME' => '',
            'SHORTNAME' => '',
            'TYPE' => '',
            'CLASS' => '',
        ];

        // NEED CLEAN AND REFACTO IF WE WANT MORE FIELDS
        foreach ($this->iniReader->getData($filePath) as $line) {
            // Check the entry type
            if (array_key_exists($line['key'], $entryValues)) {
                // Safety to ignore non matching entries (good expected order)
                if (false === $mayBe && 'NAME' !== $line['key']) {
                    foreach ($entryValues as &$content) {
                        $content = '';
                    }
                    unset($content);

                    continue;
                }

                // First expected key
                if ('NAME' === $line['key']) {
                    $mayBe = true;
                }

                $entryValues[$line['key']] = $line['value'];

                // Last expected key
                if ($mayBe && 'CLASS' === $line['key']) {
                    // Check that all the var are filled
                    $allFieldValid = true;
                    foreach ($entryValues as $content) {
                        if ('' === $content) {
                            foreach ($entryValues as &$varContent) {
                                $varContent = '';
                            }
                            unset($varContent);
                            $allFieldValid = false;
                            break;
                        }
                    }

                    $mayBe = false;

                    if (false === $allFieldValid) {
                        continue; // Another security
                    }

                    // Create SHIP here
                    $ships[] = new Ship($entryValues);

                    // clean variables
                    foreach ($entryValues as &$varContent) {
                        $varContent = '';
                    }
                    unset($varContent);
                }
            }
        }

        return $ships;
    }
}
