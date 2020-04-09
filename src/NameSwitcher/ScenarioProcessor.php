<?php

declare(strict_types=1);

namespace App\Core\NameSwitcher;

use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\SideDetector;
use App\Core\NameSwitcher\Dictionary\DictionaryFactory;
use App\Core\NameSwitcher\Switcher\SwitcherFactory;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Switcher\SwitcherInterface;
use App\NameSwitcher\Transformer\CorrespondenceWriter;

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 */
class ScenarioProcessor
{
    private ScenarioRepository $scenarioRepository;
    private SwitcherFactory $switcherFactory;
    private CorrespondenceWriter $correspondenceWriter;
    private DictionaryFactory $dictionaryFactory;
    private ScenarioUpdater $scenarioUpdater;
    private FleetLevelExperienceDetector $levelExperienceDetector;
    private SideDetector $sideDetector;
    private string $fsScenarioPath;

    public function __construct(
        ScenarioRepository $scenarioRepository,
        SwitcherFactory $switcherFactory,
        CorrespondenceWriter $correspondenceWriter,
        DictionaryFactory $dictionaryFactory,
        ScenarioUpdater $scenarioUpdater,
        FleetLevelExperienceDetector $levelExperienceDetector,
        SideDetector $sideDetector,
        string $fsPath
    ) {
        $this->scenarioRepository = $scenarioRepository;
        $this->switcherFactory = $switcherFactory;
        $this->correspondenceWriter = $correspondenceWriter;
        $this->dictionaryFactory = $dictionaryFactory;
        $this->scenarioUpdater = $scenarioUpdater;
        $this->levelExperienceDetector = $levelExperienceDetector;
        $this->sideDetector = $sideDetector;
        $this->fsScenarioPath = $fsPath . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'A_TAS_Scenario.scn';
    }

    public function convertFromTasToFs(string $scenarioName, string $oneShip, ?string $switchLevel = null): void
    {
        $scenario = $this->scenarioRepository->getOneWillAllData($scenarioName);
        $side = $this->sideDetector->detectSide($this->fsScenarioPath, $oneShip); // Important to run before the switch

        if (null === $switchLevel) {
            $switchLevel = $this->detectSwitchType($scenario, $side);
        }

        $switcher = $this->switcherFactory->getSwitcher($switchLevel);

        $correspondence = $switcher->switch(
            $this->dictionaryFactory->getDictionary($scenario->getFullPath() . DIRECTORY_SEPARATOR . 'dictionary.csv'),
            $scenario,
            $side
        );

        $this->correspondenceWriter->output($correspondence);
        $this->scenarioUpdater->updateBeforeFs($correspondence, $this->fsScenarioPath, $this->backupFsScenario());
    }

    public function convertFromFsToTas(): void
    {
        $this->scenarioUpdater->updateAfterFs();
    }

    protected function detectSwitchType(Scenario $scenario, string $side): string
    {
        $fleetCrewLevel = $this->levelExperienceDetector->getFleetLevel($scenario, $side);

        return SwitcherInterface::SWITCH_BASIC;
        // switch when many levels
    }

    protected function backupFsScenario(): string
    {
    }
}
