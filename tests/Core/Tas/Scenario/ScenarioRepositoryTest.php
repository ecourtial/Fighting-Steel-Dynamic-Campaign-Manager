<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       14/03/2020 (dd-mm-YYYY)
 */

use App\Core\Exception\MissingTasScenarioException;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use PHPUnit\Framework\TestCase;

class ScenarioRepositoryTest extends TestCase
{
    public function testGetAllNormal(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        $scenarios = $repo->getAll();

        static::assertTrue(array_key_exists('Bad GoebenReminiscence', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['Bad GoebenReminiscence']);
        static::assertEquals('Bad GoebenReminiscence', $scenarios['Bad GoebenReminiscence']->getName());
        static::assertTrue(array_key_exists('EmptyScenario', $scenarios));
        static::assertInstanceOf(Scenario::class, $scenarios['EmptyScenario']);
        static::assertEquals('EmptyScenario', $scenarios['EmptyScenario']->getName());
    }

    public function testLazy(): void
    {
        $repo = new ExtendedRepository($_ENV['TAS_LOCATION']);
        $repo->getAll();
        $repo->clearScenarios();
        static::assertEquals([], $repo->getAll(true));
    }

    public function testGetOneExists(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        $scenario = $repo->getOne('EmptyScenario');
        static::assertEquals('EmptyScenario', $scenario->getName());
    }

    public function testGetOneDoesNotExists(): void
    {
        $repo = new ScenarioRepository($_ENV['TAS_LOCATION']);
        try {
            $repo->getOne('Foo');
            static::fail("The 'Foo' scenario does not exist so an exception was expected");
        } catch (MissingTasScenarioException $exception) {
            static::assertEquals("Scenario 'Foo' not found", $exception->getMessage());
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
