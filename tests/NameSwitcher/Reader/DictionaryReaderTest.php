<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       29/02/2020 (dd-mm-YYYY)
 */

use App\NameSwitcher\Reader\DictionaryReader;
use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryReaderTest extends TestCase
{
    protected const RESULT_DATA = [
        [
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Richelieu',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Richelieu',
            'SimilarTo' => 'Dunkerque|Nelson',
        ],
        [
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Clemenceau',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Clemenceau',
            'SimilarTo' => 'Dunkerque|Nelson',
        ],
    ];

    public function testReader(): void
    {
        $reader = new DictionaryReader(new Etl(), new CsvExtractor());
        static::assertEquals(
            self::RESULT_DATA,
            $reader->extractData('tests/Assets/dictionary-small.csv')
        );
    }
}
