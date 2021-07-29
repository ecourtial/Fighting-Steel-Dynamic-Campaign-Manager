<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\Core\Fs\Scenario\Ship;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Fs\Scenario\Ship\ShipExtractor as FsShipExtractor;
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

        $scenario = $this->scenarioRepository->getOne('IncompleteScenarioWithNotTasShipFile');

        static::assertEquals(
            $result,
            $this->extractor->extract($scenario->getFullPath() . DIRECTORY_SEPARATOR . 'GR.scn', 'CLASS')
        );
    }

    public function testExtractionWithError(): void
    {
        try {
            $scenario = $this->scenarioRepository->getOne('Bad GoebenReminiscence');
            $this->extractor->extract($scenario->getFullPath() . DIRECTORY_SEPARATOR . 'GR.scn', 'CLASS');
            static::fail("Since the ship short name 'La Bombarde' is too long, an exception was expected");
        } catch (InvalidShipDataException $exception) {
            static::assertEquals(
                "FS Short name is too long: 'La Bombarde'",
                $exception->getMessage()
            );
        }
    }

    public function testExtractorWithRequiredBattleData(): void
    {
        $path = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'Sample'
                . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn';

        $result = $this->extractor->extract($path, 'RADARTYPE');

        // Just testing the side. Since other tests here already test the content of the ship.
        $expected = ['Blue', 'Blue', 'Blue', 'Red', 'Red'];
        $obtained = [];
        foreach ($result as $ship) {
            $obtained[] = $ship->getSide();
            static::assertEquals('Average', $ship->getNightTraining());
            static::assertEquals('Normal', $ship->getCrewFatigue());
            if ('Gneisenau' === $ship->getName()) {
                static::assertEquals('Veteran', $ship->getCrewQuality());
            } elseif ('Scharnhorst' === $ship->getName()) {
                static::assertEquals('Elite', $ship->getCrewQuality());
            } else {
                static::assertEquals('Average', $ship->getCrewQuality());
            }
        }

        static::assertEquals($expected, $obtained);
    }
}
