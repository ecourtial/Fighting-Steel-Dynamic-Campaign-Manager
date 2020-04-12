<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\NameSwitcher\Dictionary;

use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryReader
{
    protected CsvExtractor $csvExtractor;

    public function __construct(CsvExtractor $csvExtractor)
    {
        $this->csvExtractor = $csvExtractor;
    }

    /** @return \Generator<array> */
    public function extractData(string $file): \Generator
    {
        $this->csvExtractor->input($file)->options(['delimiter' => ';', 'throwError' => true]);

        foreach ($this->csvExtractor->extract() as $row) {
            /* @var \Wizaplace\Etl\Row $row */
            yield $row->toArray();
        }
    }
}
