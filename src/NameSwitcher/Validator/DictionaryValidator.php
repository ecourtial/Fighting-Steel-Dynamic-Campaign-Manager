<?php

declare(strict_types=1);

/**
 * This class validate the FORMAT of a dictionary.
 *
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Validator;

use App\NameSwitcher\Exception\InvalidDictionaryException;
use App\NameSwitcher\Model\Ship;
use App\NameSwitcher\Reader\DictionaryReader;

class DictionaryValidator
{
    protected DictionaryReader $dictionaryReader;

    public function __construct(DictionaryReader $dictionaryReader)
    {
        $this->dictionaryReader = $dictionaryReader;
    }

    /** @return string[] */
    public function validate(string $fullPath): array
    {
        $errors = [];
        $lineCount = 2; // Because the header doesn't count
        $ships = [];
        $extractionIsStarted = false;

        try {
            foreach ($this->dictionaryReader->extractData($fullPath) as $line) {
                try {
                    $extractionIsStarted = true;
                    $ship = new Ship($line);
                    if (array_key_exists($ship->getTasName(), $ships)) {
                        throw new InvalidDictionaryException("The name '{$ship->getTasName()}' is already used at line #" . $ships[$ship->getTasName()]);
                    }
                    $ships[$ship->getTasName()] = $lineCount;
                } catch (\Exception $exception) {
                    $errors[] = "Error at line #{$lineCount}. " . $exception->getMessage();
                }
                $lineCount++;
            }
        } catch (\Exception $exception) {
            if ($extractionIsStarted) {
                $message = $exception->getMessage();
            } else {
                $message = 'Error during the dictionary extraction: ' . $exception->getMessage();
            }

            $errors[] = $message;
        }

        return $errors;
    }
}
