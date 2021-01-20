<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator;

use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;
use App\NameSwitcher\Dictionary\DictionaryReader;
use App\ScenarioGenerator\Engine\DictionaryExtractor;

class DictionaryTest extends TestCase
{
    /** @dataProvider shipDataProvider */
    public function testVerifyShipFileExistence(string $path): void
    {
        static $testedPaths = [];

        if (true === in_array($path, $testedPaths)) {
            static::assertTrue(true);

            return;
        }

        $testedPaths[] = $path;

        if (true === file_exists($path)) {
            static::assertTrue(true); // Because I want a specific error message
        } else {
            static::fail("Ship data file '$path' not found.");
        }
    }

    /** Tests the dictionary extraction and that each ship file is present */
    public function shipDataProvider(): array
    {
        $dictionary = new DictionaryReader(new CsvExtractor());
        $dictionaryExtractor = new DictionaryExtractor($dictionary);

        $ships = $dictionaryExtractor->getShipDictionary($_ENV['DATA_FOLDER'] . DIRECTORY_SEPARATOR . 'FSP10.3_Ship_List.csv');
        $shipsRootDir = $_ENV['DATA_FOLDER'] . DIRECTORY_SEPARATOR . 'ships' . DIRECTORY_SEPARATOR;
        $paths = [];

        foreach ($ships as $currentSide => $naviesOfThisSide) {
            foreach ($naviesOfThisSide as $type => $shipsForThisNavy) {
                foreach ($shipsForThisNavy as $ship) {
                    $paths[] = [$shipsRootDir . $currentSide . DIRECTORY_SEPARATOR . $type
                        . DIRECTORY_SEPARATOR . str_replace(' ', '', $ship['class']) . '.txt'];
                }
            }
        }

        return $paths;
    }
}
