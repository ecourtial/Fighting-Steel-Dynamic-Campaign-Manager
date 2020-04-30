<?php

/**
 * This class validate the FORMAT of a dictionary.
 *
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\NameSwitcher\Validator;

use App\NameSwitcher\Dictionary\DictionaryReader;
use App\NameSwitcher\Dictionary\Ship;
use App\NameSwitcher\Exception\InvalidDictionaryException;

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
        $ships = [];
        $extractionIsStarted = false;
        // Because the header doesn't count
        $lineCount = 2;

        try {
            foreach ($this->dictionaryReader->extractData($fullPath) as $line) {
                try {
                    $extractionIsStarted = true;
                    $ship = new Ship($line);
                    if (array_key_exists($ship->getTasName(), $ships)) {
                        $errorMsg = "The name '{$ship->getTasName()}' is already used at line #"
                            . $ships[$ship->getTasName()];
                        throw new InvalidDictionaryException($errorMsg);
                    }
                    $ships[$ship->getTasName()] = $lineCount;
                } catch (\Throwable $exception) {
                    $errors[] = "Error at line #{$lineCount}. " . $exception->getMessage();
                }
                $lineCount++;
            }
        } catch (\Throwable $exception) {
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
