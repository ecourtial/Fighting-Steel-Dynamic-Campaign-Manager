<?php

declare(strict_types=1);

namespace App\NameSwitcher;

use App\Core\Fs\Scenario\Ship\ShipExtractor;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\DictionaryFactory;

class ScenarioManager
{
    private ScenarioProcessor $scenarioProcessor;
    private DictionaryFactory $dictionaryFactory;
    private ScenarioRepository $scenarioRepository;
    private ShipExtractor $shipExtractor;
    private string $fsScenariosFolder;
    private string $fsScenarioPath;

    public function __construct(
        ScenarioProcessor $scenarioProcessor,
        DictionaryFactory $dictionaryFactory,
        ScenarioRepository $scenarioRepository,
        ShipExtractor $shipExtractor,
        string $fsDirectory
    ) {
        $this->scenarioProcessor = $scenarioProcessor;
        $this->dictionaryFactory = $dictionaryFactory;
        $this->scenarioRepository = $scenarioRepository;
        $this->shipExtractor = $shipExtractor;

        $this->fsScenariosFolder = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $this->fsScenarioPath = $this->fsScenariosFolder . 'A_TAS_Scenario.scn';
    }

    public function fromTasToFs(string $scenarioKey, string $oneShip, string $switchLevel): void
    {
        $scenario = $this->scenarioRepository->getOneWillAllData($scenarioKey);
        $this->scenarioProcessor->convertFromTasToFs(
            $oneShip,
            $this->dictionaryFactory->getDictionary($scenario->getDictionaryPath()),
            $this->shipExtractor->extract($this->fsScenarioPath, 'NIGHTTRAINING'),
            $this->fsScenariosFolder,
            $this->fsScenarioPath,
            $switchLevel
        );
    }

    public function fromFsToTas(): void
    {
        $this->scenarioProcessor->convertFromFsToTas($this->fsScenariosFolder, $this->fsScenarioPath);
    }
}
