<?php

declare(strict_types=1);

namespace App\Core\NameSwitcher\Dictionary;

use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryReader;

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */
class DictionaryFactory
{
    private DictionaryReader $dictionaryReader;

    public function __construct(DictionaryReader $dictionaryReader)
    {
        $this->dictionaryReader = $dictionaryReader;
    }

    public function getDictionary(string $dictionaryPath): Dictionary
    {
        $data = [];
        foreach ($this->dictionaryReader->extractData($dictionaryPath) as $element
        ) {
            $data[] = $element;
        }

        return new Dictionary($data);
    }
}
