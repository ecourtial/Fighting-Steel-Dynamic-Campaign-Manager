<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Tas\Scenario;

use App\Core\Exception\FileNotFoundException;
use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Fs\Scenario\Ship\ShipExtractor as FsShipExtractor;
use App\Core\Tas\Exception\MissingTasScenarioException;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\Core\Tas\Ship\Ship as TasShip;
use App\Core\Tas\Ship\ShipExtractor as TasShipExtractor;
use App\NameSwitcher\Exception\InvalidShipDataException;
use PHPUnit\Framework\TestCase;

class ScenarioRepositoryTest extends TestCase
{
    protected static ScenarioRepository $scenarioRepository;

    public static function setUpBeforeClass(): void
    {
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);

        static::$scenarioRepository = new ScenarioRepository(
            $_ENV['TAS_LOCATION'],
            new IniReader($textReader),
            new TasShipExtractor($iniReader),
            new FsShipExtractor($iniReader)
        );
    }

    public function testGetAllNormal(): void
    {
        $scenarios = static::$scenarioRepository->getAll(false);

        static::assertTrue(array_key_exists('Bad GoebenReminiscence', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['Bad GoebenReminiscence']);
        static::assertEquals('Bad GoebenReminiscence', $scenarios['Bad GoebenReminiscence']->getName());
        static::assertEquals('GR.scn', $scenarios['Bad GoebenReminiscence']->getShipDataFile());

        static::assertTrue(array_key_exists('IncompleteScenarioWithNotTasShipFile', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['IncompleteScenarioWithNotTasShipFile']);
        static::assertEquals('IncompleteScenarioWithNotTasShipFile', $scenarios['IncompleteScenarioWithNotTasShipFile']->getName());
    }

    public function testLazy(): void
    {
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        $repo = new ExtendedRepository($_ENV['TAS_LOCATION'], $iniReader, new TasShipExtractor($iniReader), new FsShipExtractor($iniReader));
        $scenarios = $repo->getAll();
        static::assertEquals(5, count($scenarios));

        $scenarios = $repo->getAll();
        static::assertEquals(5, count($scenarios));

        $repo->clearScenarios();
        static::assertEquals([], $repo->getAll());

        $scenarios = $repo->getAll(false);
        static::assertEquals(5, count($scenarios));
    }

    public function testGetOneExists(): void
    {
        $scenario = static::$scenarioRepository->getOne('IncompleteScenarioWithNotTasShipFile');
        static::assertEquals('IncompleteScenarioWithNotTasShipFile', $scenario->getName());

        $expectedFullPath = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'IncompleteScenarioWithNotTasShipFile';
        static::assertEquals($expectedFullPath, $scenario->getFullPath());
    }

    public function testGetOneDoesNotExists(): void
    {
        try {
            static::$scenarioRepository->getOne('Foo');
            static::fail("The 'Foo' scenario does not exist so an exception was expected");
        } catch (MissingTasScenarioException $exception) {
            static::assertEquals("Scenario 'Foo' not found", $exception->getMessage());
        }
    }

    // Usually an empty folder
    public function testGetScenarioWithErrorsWhenConfigScenarioFileIsMissing(): void
    {
        try {
            static::$scenarioRepository->getAll(false, false)['EmptyScenario'];
            static::fail("The 'EmptyScenario' scenario has no config file so an exception was expected");
        } catch (FileNotFoundException $exception) {
            static::assertEquals("Impossible to read the content of the file 'tests/Assets/TAS/Scenarios/EmptyScenario/ScenarioInfo.cfg'.", $exception->getMessage());
        }
    }

    public function testGetScenarioWithIncompleteConfig(): void
    {
        try {
            static::$scenarioRepository->getOne('ScenarioInvalidConfig');
            static::fail("The 'ScenarioInvalidConfig' scenario has an incomplete config file so an exception was expected");
        } catch (InvalidInputException $exception) {
            static::assertEquals("Scenario info not found : 'Shipdatafile' in 'tests/Assets/TAS/Scenarios/ScenarioInvalidConfig/ScenarioInfo.cfg'", $exception->getMessage());
        }
    }

    public function testGetOneWithAllDataWithError(): void
    {
        try {
            static::$scenarioRepository->getOneWillAllData('Bad GoebenReminiscence');
            static::fail("Since the ship short name 'La Bombarde' is too long, an exception was expected");
        } catch (InvalidShipDataException $exception) {
            static::assertEquals(
                "FS Short name is too long: 'La Bombarde'",
                $exception->getMessage()
            );
        }
    }

    public function testGetOneWillAllDataGood(): void
    {
        $fsShips = [
            'Scharnhorst' => new Ship(['NAME' => 'Scharnhorst', 'SHORTNAME' => 'Scharnhrst', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
            'Gneisenau' => new Ship(['NAME' => 'Gneisenau', 'SHORTNAME' => 'Gneisenau', 'TYPE' => 'BC', 'CLASS' => 'Scharnhorst']),
            'Algerie' => new Ship(['NAME' => 'Algerie', 'SHORTNAME' => 'Algerie', 'TYPE' => 'CA', 'CLASS' => 'Zara']),
            'Bretagne' => new Ship(['NAME' => 'Bretagne', 'SHORTNAME' => 'Bretagne', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            'Provence' => new Ship(['NAME' => 'Provence', 'SHORTNAME' => 'Provence', 'TYPE' => 'BB', 'CLASS' => 'Bretagne']),
            'La Palme' => new Ship(['NAME' => 'La Palme', 'SHORTNAME' => 'La Palme', 'TYPE' => 'DD', 'CLASS' => 'Le Fantasque']),
        ];

        $alliedShips = [
            'Bretagne' => new TasShip('Bretagne', 'BB'),
            'Provence' => new TasShip('Provence', 'BB'),
            'Algerie' => new TasShip('Algerie', 'CA'),
            'La Palme' => new TasShip('La Palme', 'DD'),
        ];

        $axisShips = [
            'Scharnhorst' => new TasShip('Scharnhorst', 'BC'),
            'Gneisenau' => new TasShip('Gneisenau', 'BC'),
        ];

        $scenario = static::$scenarioRepository->getOneWillAllData('Good Scenario');
        static::assertEquals($fsShips, $scenario->getFsShips());
        static::assertEquals($alliedShips, $scenario->getTasShips('Allied'));
        static::assertEquals($axisShips, $scenario->getTasShips('Axis'));
    }
}

class ExtendedRepository extends ScenarioRepository
{
    public function clearScenarios(): void
    {
        $this->scenarios = [];
    }
}
