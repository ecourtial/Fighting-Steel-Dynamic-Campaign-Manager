<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Scenario;

use App\Core\Exception\MissingTasScenarioException;

class ScenarioRepository
{
    protected string $scenarioDirectory;

    /** @var \App\Core\Tas\Scenario\Scenario[]|null */
    protected ?array $scenarios = null;

    public function __construct(string $tasDirectory)
    {
        $this->scenarioDirectory = $tasDirectory . DIRECTORY_SEPARATOR . 'Scenarios';
    }

    /** @return Scenario[] */
    public function getAll(bool $lazy = true): array
    {
        if (true === $lazy && null !== $this->scenarios) {
            return $this->scenarios;
        }

        $this->scenarios = [];
        clearstatcache();
        $folderContent = scandir($this->scenarioDirectory);

        foreach ($folderContent as $element) {
            if (
                is_dir($this->scenarioDirectory . DIRECTORY_SEPARATOR . $element)
                && preg_match('/^[a-zA-Z0-9 ]*$/', $element)
            ) {
                $exploded = explode(DIRECTORY_SEPARATOR, $element);
                $scenarioKey = array_pop($exploded);
                $this->scenarios[$scenarioKey] = new Scenario($scenarioKey, $element);
            }
        }

        return $this->scenarios;
    }

    public function getOne(string $name): Scenario
    {
        if (false === array_key_exists($name, $this->getAll())) {
            throw new MissingTasScenarioException($name);
        }

        return $this->scenarios[$name];
    }
}
