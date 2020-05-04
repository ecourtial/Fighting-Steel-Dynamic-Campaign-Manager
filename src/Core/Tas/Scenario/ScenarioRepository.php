<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       16/03/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\Tas\Scenario;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\Fs\Scenario\Ship\ShipExtractor as FsShipExtractor;
use App\Core\Tas\Exception\MissingTasScenarioException;
use App\Core\Tas\Savegame\Savegame;
use App\Core\Tas\Ship\ShipExtractor as TasShipExtractor;

class ScenarioRepository
{
    public const SHIP_DATA_FILE_KEY = 'Shipdatafile';

    /** @var \App\Core\Tas\Scenario\Scenario[]|null */
    protected ?array $scenarios = null;

    protected string $scenarioDirectory;
    protected IniReader $iniReader;
    protected TasShipExtractor $tasShipExtractor;
    protected FsShipExtractor $fsShipExtractor;

    public function __construct(
        string $tasDirectory,
        IniReader $iniReader,
        TasShipExtractor $tasShipExtractor,
        FsShipExtractor $fsShipExtractor
    ) {
        $this->scenarioDirectory = $tasDirectory;
        $this->iniReader = $iniReader;
        $this->tasShipExtractor = $tasShipExtractor;
        $this->fsShipExtractor = $fsShipExtractor;
    }

    /** @return Scenario[] */
    public function getAll(bool $lazy = true, bool $ignoreUnreadable = true): array
    {
        if (true === $lazy && null !== $this->scenarios) {
            return $this->scenarios;
        }

        $this->scenarios = [];
        $folderContent = scandir($this->scenarioDirectory);

        foreach ($folderContent as $element) {
            $scenarioFullPath = $this->scenarioDirectory . DIRECTORY_SEPARATOR . $element;
            if (
                is_dir($scenarioFullPath)
                && preg_match('/^[a-zA-Z0-9 ]*$/', $element)
                && 0 === preg_match(Savegame::PATH_REGEX, $element)
                && 'Autosave' !== $element
            ) {
                $exploded = explode(DIRECTORY_SEPARATOR, $element);
                $scenarioKey = array_pop($exploded);
                try {
                    $scenarioInfoFile = $this->getShipDataFile($scenarioFullPath);
                } catch (\Throwable $exception) {
                    if ($ignoreUnreadable) {
                        continue;
                    }
                    throw $exception;
                }

                $this->scenarios[$scenarioKey] = new Scenario(
                    $scenarioKey,
                    $scenarioFullPath,
                    $scenarioInfoFile
                );
            }
        }

        return $this->scenarios;
    }

    /** Return the scenario object with only its metadata */
    public function getOne(string $name): Scenario
    {
        $scenarioFullPath = $this->scenarioDirectory . DIRECTORY_SEPARATOR . $name;

        if (false === is_dir($scenarioFullPath)) {
            throw new MissingTasScenarioException($name);
        }

        return new Scenario(
            $name,
            $scenarioFullPath,
            $this->getShipDataFile($scenarioFullPath)
        );
    }

    /** Return the scenario object with all its data */
    public function getOneWillAllData(string $name): Scenario
    {
        $scenario = $this->getOne($name);
        $scenario->setFsShips(
            $this->fsShipExtractor->extract($scenario->getShipDataFile(), 'CLASS')
        );
        $scenario->setTasShips('Axis', $this->tasShipExtractor->extract($scenario, 'Axis'));
        $scenario->setTasShips('Allied', $this->tasShipExtractor->extract($scenario, 'Allied'));

        return $scenario;
    }

    private function getShipDataFile(string $fullPath): string
    {
        $fullPath .= DIRECTORY_SEPARATOR . 'ScenarioInfo.cfg';

        foreach ($this->iniReader->getData($fullPath) as $line) {
            if (static::SHIP_DATA_FILE_KEY === $line['key']) {
                return $line['value'];
            }
        }

        $message = "Scenario info not found : '" . static::SHIP_DATA_FILE_KEY . "' in '{$fullPath}'";
        throw new InvalidInputException($message);
    }
}
