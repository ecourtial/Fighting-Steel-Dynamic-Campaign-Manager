<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

namespace App\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\Tas\Exception\MissingTasScenarioException;

class ScenarioRepository
{
    protected string $scenarioDirectory;

    /** @var \App\Core\Tas\Scenario\Scenario[]|null */
    protected ?array $scenarios = null;

    protected IniReader $iniReader;

    public function __construct(string $tasDirectory, IniReader $iniReader)
    {
        $this->scenarioDirectory = $tasDirectory . DIRECTORY_SEPARATOR . 'Scenarios';
        $this->iniReader = $iniReader;
    }

    /** @return Scenario[] */
    public function getAll(bool $lazy = true, bool $ignoreUnreadable = true): array
    {
        if (true === $lazy && null !== $this->scenarios) {
            return $this->scenarios;
        }

        $this->scenarios = [];
        clearstatcache();
        $folderContent = scandir($this->scenarioDirectory);

        foreach ($folderContent as $element) {
            $scenarioFullPath = $this->scenarioDirectory . DIRECTORY_SEPARATOR . $element;
            if (
                is_dir($scenarioFullPath)
                && preg_match('/^[a-zA-Z0-9 ]*$/', $element)
            ) {
                $exploded = explode(DIRECTORY_SEPARATOR, $element);
                $scenarioKey = array_pop($exploded);
                try {
                    [$shipFile] = $this->extractScenarioInfo($scenarioFullPath);
                } catch (\Exception $exception) {
                    if ($ignoreUnreadable) {
                        continue;
                    }
                    throw $exception;
                }

                $this->scenarios[$scenarioKey] = new Scenario(
                    $scenarioKey,
                    $scenarioFullPath,
                    $shipFile
                );
            }
        }

        return $this->scenarios;
    }

    public function getOne(string $name): Scenario
    {
        $scenarioFullPath = $this->scenarioDirectory . DIRECTORY_SEPARATOR . $name;

        if (false === is_dir($scenarioFullPath)) {
            throw new MissingTasScenarioException($name);
        }

        [$shipFile] = $this->extractScenarioInfo($scenarioFullPath);

        return new Scenario(
            $name,
            $scenarioFullPath,
            $shipFile
        );
    }

    /** @return string[] */
    protected function extractScenarioInfo(string $fullPath): array
    {
        $fullPath .= DIRECTORY_SEPARATOR . 'ScenarioInfo.cfg';
        $scenarioInfo = [
            'Shipdatafile' => '',
        ];

        foreach ($this->iniReader->getData($fullPath) as $line) {
            // Keep the order
            if ('Shipdatafile' === $line['key']) {
                $scenarioInfo['Shipdatafile'] = $line['value'];
            }
        }

        $newData = [];

        foreach ($scenarioInfo as $key => $value) {
            if ('' === $value) {
                throw new InvalidInputException("Scenario info not found : '{$key}' in '{$fullPath}'");
            }
            $newData[] = $value;
        }

        return $newData;
    }
}
