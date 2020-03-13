<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       13/03/2020 (dd-mm-YYYY)
 */

use App\NameSwitcher\Model\Dictionary;
use App\NameSwitcher\Model\Ship;
use App\NameSwitcher\Reader\DictionaryReader;
use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryTest extends TestCase
{
    protected Ship $ship1;
    protected Ship $ship2;
    protected static array $rawData;

    public static function setUpBeforeClass()
    {
        $reader = new DictionaryReader(new Etl(), new CsvExtractor());
        static::$rawData = $reader->extractData('tests/Assets/dictionary-small.csv');
    }

    public function setUp()
    {
        $this->ship1 = new Ship([
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Richelieu',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Richelieu',
            'SimilarTo' => 'Dunkerque|Nelson',
        ]);

        $this->ship2 = new Ship([
            'Type' => 'BB',
            'Class' => 'Richelieu',
            'TasName' => 'Clemenceau',
            'FsName' => 'Richelieu',
            'FsShortName' => 'Clemenceau',
            'SimilarTo' => 'Dunkerque|Nelson',
        ]);
    }

    public function testGetShipsList(): void
    {
        $dictionary = new Dictionary(static::$rawData);
        static::assertEquals(
            [$this->ship1, $this->ship2],
            $dictionary->getShipsList()
        );
    }
}
