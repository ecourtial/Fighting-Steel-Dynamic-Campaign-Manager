<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\NameSwitcher\Dictionary;

use App\NameSwitcher\Dictionary\DictionaryReader;
use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryReaderTest extends TestCase
{
    protected const RESULT_DATA = [
        [
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Richelieu',
            'FsClass' => 'Richelieu',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Richelieu',
            'SimilarTo' => 'Dunkerque|Nelson',
        ],
        [
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Clemenceau',
            'FsClass' => 'Richelieu',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Clemenceau',
            'SimilarTo' => 'Dunkerque|Nelson',
        ],
    ];

    public function testReader(): void
    {
        $reader = new DictionaryReader(new CsvExtractor());
        $data = [];
        foreach ($reader->extractData('tests/Assets/dictionary-small.csv') as $element) {
            $data[] = $element;
        }

        static::assertEquals(self::RESULT_DATA, $data);
    }
}
