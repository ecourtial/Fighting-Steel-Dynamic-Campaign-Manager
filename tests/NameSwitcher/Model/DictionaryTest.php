<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       13/03/2020 (dd-mm-YYYY)
 */

use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Exception\MoreThanOneShipException;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Model\Dictionary;
use App\NameSwitcher\Model\Ship;
use App\NameSwitcher\Reader\DictionaryReader;
use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryTest extends TestCase
{
    protected Ship $ship1;
    protected Ship $ship2;
    protected static array $rawData;

    public static function setUpBeforeClass()
    {
        $reader = new DictionaryReader(new CsvExtractor());
        static::$rawData = [];
        foreach ($reader->extractData('tests/Assets/dictionary-small.csv') as $element) {
            static::$rawData[] = $element;
        }
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

    public function testMissingField(): void
    {
        $data = [
            [
                'Type' => 'BB',
                'Class' => 'Richelieu',
                'TasName' => 'Clemenceau',
                'FsName' => 'Richelieu',
                'SimilarTo' => 'Dunkerque|Nelson',
            ],
        ];

        try {
            new Dictionary($data);
            static::fail('Since the input data was invalid, an exception was expected');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals(
                "Field 'FsShortName' is missing. Given data was: 'Type' => 'BB','Class' => 'Richelieu','TasName' => 'Clemenceau','FsName' => 'Richelieu','SimilarTo' => 'Dunkerque|Nelson'",
                $exception->getMessage()
            );
        }
    }

    public function testGetShipsList(): void
    {
        $dictionary = new Dictionary(static::$rawData);
        static::assertEquals(
            [$this->ship1, $this->ship2],
            $dictionary->getShipsList()
        );
    }

    public function testSearchInList(): void
    {
        $dictionary = new Dictionary(static::$rawData);

        static::assertEquals(
            [$this->ship1, $this->ship2],
            $dictionary->searchInList(['Type' => 'BB'])
        );

        static::assertEquals(
            [$this->ship1, $this->ship2],
            $dictionary->searchInList(['Type' => 'BB', 'Class' => 'Richelieu'])
        );

        static::assertEquals(
            [$this->ship2],
            $dictionary->searchInList(['Type' => 'BB', 'TasName' => 'Clemenceau'])
        );

        static::assertEquals(
            [$this->ship1, $this->ship2],
            $dictionary->searchInList(['SimilarTo' => 'Nelson'])
        );

        try {
            $dictionary->searchInList(['SimilarTo' => 'Hood']);
            static::fail('An exception was expected since no ship matches the criteria');
        } catch (NoShipException $exception) {
            $expected = "No ship found matching the required criteria: 'SimilarTo' => 'Hood'";
            static::assertEquals($expected, $exception->getMessage());
        }
    }

    public function testRandomWithCriteria(): void
    {
        $dictionary = new Dictionary(static::$rawData);

        static::assertEquals(
            $this->ship2,
            $dictionary->randomWithCriteria(['Type' => 'BB', 'TasName' => 'Clemenceau'])
        );

        $ship = $dictionary->randomWithCriteria(['Type' => 'BB']);
        if ($ship == $this->ship1 && $ship == $this->ship2) {
            static::fail('The result is not the one expected');
        }
    }

    public function testFindOneShip(): void
    {
        $dictionary = new Dictionary(static::$rawData);

        // Normal case
        static::assertEquals($this->ship2, $dictionary->findOneShip(['TasName' => 'Clemenceau']));

        // Normal with random
        $ship = $dictionary->findOneShip(['Type' => 'BB'], true);
        if ($ship == $this->ship1 && $ship == $this->ship2) {
            static::fail('The result is not the one expected');
        }

        // More than one result
        try {
            $dictionary->findOneShip(['Type' => 'BB']);
            static::fail('An exception was expected since more than one ship match the criteria');
        } catch (MoreThanOneShipException $exception) {
            $expected = "More than one result found for the given criteria: 'Type' => 'BB'";
            static::assertEquals($expected, $exception->getMessage());
        }
    }
}
