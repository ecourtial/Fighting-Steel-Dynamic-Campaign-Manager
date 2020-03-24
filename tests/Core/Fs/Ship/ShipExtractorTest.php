<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Fs\Ship;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\Ship\Ship;
use App\Core\Fs\Ship\ShipExtractor as FsShipExtractor;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\Core\Tas\Ship\ShipExtractor as TasShipExtractor;
use App\NameSwitcher\Exception\InvalidShipDataException;
use PHPUnit\Framework\TestCase;

class ShipExtractorTest extends TestCase
{
    protected FsShipExtractor $extractor;
    protected ScenarioRepository $scenarioRepository;

    public function setUp(): void
    {
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        $this->extractor = new FsShipExtractor($iniReader);
        $tasShipExtractor = new TasShipExtractor($iniReader);

        $this->scenarioRepository = new ScenarioRepository(
            $_ENV['TAS_LOCATION'],
            $iniReader,
            $tasShipExtractor,
            $this->extractor
        );
    }

    public function testNormalExtraction(): void
    {
        $result = [
            new Ship(
                [
                    'NAME' => 'Scharnhorst',
                    'SHORTNAME' => 'Scharnhrst',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
            new Ship(
                [
                    'NAME' => 'Gneisenau',
                    'SHORTNAME' => 'Gneisenau',
                    'TYPE' => 'BC',
                    'CLASS' => 'Scharnhorst',
                ]
            ),
            new Ship(
                [
                    'NAME' => 'Algerie',
                    'SHORTNAME' => 'Algerie',
                    'TYPE' => 'CA',
                    'CLASS' => 'Zara',
                ]
            ),
            new Ship(
                [
                    'NAME' => 'Bretagne',
                    'SHORTNAME' => 'Bretagne',
                    'TYPE' => 'BB',
                    'CLASS' => 'Bretagne',
                ]
            ),
        ];

        static::assertEquals(
            $result,
            $this->extractor->extract($this->scenarioRepository->getOne('IncompleteScenarioWithNotTasShipFile'))
        );
    }

    public function testExtractionWithError(): void
    {
        try {
            $this->extractor->extract($this->scenarioRepository->getOne('Bad GoebenReminiscence'));
            static::fail("Since the ship short name 'La Bombarde' is too long, an exception was expected");
        } catch (InvalidShipDataException $exception) {
            static::assertEquals(
                "FS Short name is too long: 'La Bombarde'",
                $exception->getMessage()
            );
        }
    }
}
