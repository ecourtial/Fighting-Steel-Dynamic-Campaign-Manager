<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Reader;

use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryReader
{
    protected CsvExtractor $csvExtractor;

    public function __construct(CsvExtractor $csvExtractor)
    {
        $this->csvExtractor = $csvExtractor;
    }

    /** @return string[][] */
    public function extractData(string $file): \Generator
    {
        $this->csvExtractor->input($file)->options(['delimiter' => ';']);

        foreach ($this->csvExtractor->extract() as $row) {
            /* @var \Wizaplace\Etl\Row $row */
            yield $row->toArray();
        }
    }
}
