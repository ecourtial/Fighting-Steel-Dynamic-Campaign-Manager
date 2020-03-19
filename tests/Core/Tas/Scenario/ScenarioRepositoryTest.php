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
use App\Core\Tas\Exception\MissingTasScenarioException;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use PHPUnit\Framework\TestCase;

class ScenarioRepositoryTest extends TestCase
{
    protected static IniReader $iniReader;

    public static function setUpBeforeClass()
    {
        $textReader = new TextFileReader();
        static::$iniReader = new IniReader($textReader);
    }

    public function testGetAllNormal(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        $scenarios = $repo->getAll();

        static::assertTrue(array_key_exists('Bad GoebenReminiscence', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['Bad GoebenReminiscence']);
        static::assertEquals('Bad GoebenReminiscence', $scenarios['Bad GoebenReminiscence']->getName());
        static::assertTrue(array_key_exists('IncompleteScenarioWithNotTasShipFile', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['IncompleteScenarioWithNotTasShipFile']);
        static::assertEquals('IncompleteScenarioWithNotTasShipFile', $scenarios['IncompleteScenarioWithNotTasShipFile']->getName());
    }

    public function testLazy(): void
    {
        $repo = new ExtendedRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        $repo->getAll();
        $repo->clearScenarios();
        static::assertEquals([], $repo->getAll(true));
    }

    public function testGetOneExists(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        $scenario = $repo->getOne('IncompleteScenarioWithNotTasShipFile');
        static::assertEquals('IncompleteScenarioWithNotTasShipFile', $scenario->getName());

        $expectedFullPath = $_ENV['TAS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'IncompleteScenarioWithNotTasShipFile';
        static::assertEquals($expectedFullPath, $scenario->getFullPath());
    }

    public function testGetOneDoesNotExists(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        try {
            $repo->getOne('Foo');
            static::fail("The 'Foo' scenario does not exist so an exception was expected");
        } catch (MissingTasScenarioException $exception) {
            static::assertEquals("Scenario 'Foo' not found", $exception->getMessage());
        }
    }

    // Usually an empty folder
    public function testGetScenarioWithErrorsWhenConfigScenarioFileIsMissing(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        try {
            $repo->getAll(false, false)['EmptyScenario'];
            static::fail("The 'EmptyScenario' scenario has no config file so an exception was expected");
        } catch (FileNotFoundException $exception) {
            static::assertEquals("Impossible to read the content of the file 'tests/Assets/Tas/Scenarios/EmptyScenario/ScenarioInfo.cfg'.", $exception->getMessage());
        }
    }

    public function testGetScenarioWithIncompleteConfig(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION'], static::$iniReader);
        try {
            $repo->getOne('ScenarioInvalidConfig');
            static::fail("The 'ScenarioInvalidConfig' scenario has an incomplete config file so an exception was expected");
        } catch (InvalidInputException $exception) {
            static::assertEquals("Scenario info not found : 'Shipdatafile' in 'tests/Assets/Tas/Scenarios/ScenarioInvalidConfig/ScenarioInfo.cfg'", $exception->getMessage());
        }
    }
}

class ExtendedRepository extends ScenarioRepository
{
    public function clearScenarios(): void
    {
        $this->scenarios = [];
    }
}
