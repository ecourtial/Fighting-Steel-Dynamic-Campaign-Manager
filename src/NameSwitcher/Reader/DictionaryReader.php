<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       11/03/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Reader;

use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryReader
{
    protected Etl $etl;
    protected CsvExtractor $csvExtractor;

    public function __construct(Etl $etl, CsvExtractor $csvExtractor)
    {
        $this->etl = $etl;
        $this->csvExtractor = $csvExtractor;
    }

    /** @return string[] */
    public function extractData(string $file): array
    {
        return $this->etl
            ->extract($this->csvExtractor, $file, ['delimiter' => ';'])
            ->toArray();
    }
}
