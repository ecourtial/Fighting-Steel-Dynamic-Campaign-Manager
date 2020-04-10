<?php

declare(strict_types=1);

namespace App\NameSwitcher;

use App\Core\Exception\CoreException;
use App\Core\File\IniReader;
use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\SideDetector;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\Switcher\SwitcherFactory;
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
    private IniReader $iniReader;
    private string $fsScenarioPath;
    private string $fsScenarioFolder;

    public function __construct(
        ScenarioRepository $scenarioRepository,
        SwitcherFactory $switcherFactory,
        CorrespondenceWriter $correspondenceWriter,
        DictionaryFactory $dictionaryFactory,
        ScenarioUpdater $scenarioUpdater,
        FleetLevelExperienceDetector $levelExperienceDetector,
        SideDetector $sideDetector,
        IniReader $iniReader,
        string $fsPath
    ) {
        $this->scenarioRepository = $scenarioRepository;
        $this->switcherFactory = $switcherFactory;
        $this->correspondenceWriter = $correspondenceWriter;
        $this->dictionaryFactory = $dictionaryFactory;
        $this->scenarioUpdater = $scenarioUpdater;
        $this->levelExperienceDetector = $levelExperienceDetector;
        $this->iniReader = $iniReader;

        $this->sideDetector = $sideDetector;
        $this->fsScenarioFolder = $fsPath . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $this->fsScenarioPath = $this->fsScenarioFolder . 'A_TAS_Scenario.scn';
    }

    public function convertFromTasToFs(string $scenarioName, string $oneShip, ?string $switchLevel = null): void
    {
        $this->backupFsScenario();
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
        $this->scenarioUpdater->updateBeforeFs($correspondence, $this->fsScenarioPath);
    }

    public function convertFromFsToTas(): void
    {
        $content = [];
        foreach ($this->iniReader->getData($this->fsScenarioFolder . 'correspondence.ini') as $entry) {
            $content[$entry['key']] = $entry['value'];
        }
        $this->scenarioUpdater->updateAfterFs($content, $this->fsScenarioPath);
    }

    protected function detectSwitchType(Scenario $scenario, string $side): string
    {
        //$fleetCrewLevel = $this->levelExperienceDetector->getFleetLevel($scenario, $side);
        // And complete the unit test of the current class to check this case
        return SwitcherInterface::SWITCH_BASIC;
        // switch when many levels
    }

    protected function backupFsScenario(): void
    {
        $date = (new \DateTime())->format('Y-m-d-H-i-s');
        $dest = $this->fsScenarioFolder . 'Backup' . DIRECTORY_SEPARATOR . $date . '.scn.bak';

        try {
            copy($this->fsScenarioPath, $dest);
        } catch (\Throwable $exception) {
            throw new CoreException('Impossible to backup the FS scenario: ' . $exception->getMessage());
        }
    }
}
