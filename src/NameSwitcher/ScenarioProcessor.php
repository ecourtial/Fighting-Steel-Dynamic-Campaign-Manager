<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 */

declare(strict_types=1);

namespace App\NameSwitcher;

use App\Core\Exception\CoreException;
use App\Core\File\IniReader;
use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\Core\Fs\Scenario\SideDetector;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Switcher\SwitcherFactory;
use App\NameSwitcher\Switcher\SwitcherInterface;
use App\NameSwitcher\Transformer\CorrespondenceWriter;

class ScenarioProcessor
{
    private SwitcherFactory $switcherFactory;
    private CorrespondenceWriter $correspondenceWriter;
    private ScenarioUpdater $scenarioUpdater;
    private FleetLevelExperienceDetector $levelExperienceDetector;
    private SideDetector $sideDetector;
    private IniReader $iniReader;
    private string $fsScenarioPath;
    private string $fsScenarioFolder;

    public function __construct(
        SwitcherFactory $switcherFactory,
        CorrespondenceWriter $correspondenceWriter,
        ScenarioUpdater $scenarioUpdater,
        FleetLevelExperienceDetector $levelExperienceDetector,
        SideDetector $sideDetector,
        IniReader $iniReader,
        string $fsDirectory
    ) {
        $this->switcherFactory = $switcherFactory;
        $this->correspondenceWriter = $correspondenceWriter;
        $this->scenarioUpdater = $scenarioUpdater;
        $this->levelExperienceDetector = $levelExperienceDetector;
        $this->iniReader = $iniReader;

        $this->sideDetector = $sideDetector;
        $this->fsScenarioFolder = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $this->fsScenarioPath = $this->fsScenarioFolder . 'A_TAS_Scenario.scn';
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $scenarioShips
     */
    public function convertFromTasToFs(
        string $oneShip,
        Dictionary $dictionary,
        array $scenarioShips,
        ?string $switchLevel = null
    ): void {
        $this->backupFsScenario();
        $side = $this->sideDetector->detectSide($scenarioShips, $oneShip); // Important to run before the switch

        if (null === $switchLevel) {
            $switchLevel = $this->detectSwitchType($scenarioShips, $side);
        }

        $switcher = $this->switcherFactory->getSwitcher($switchLevel);

        $correspondence = $switcher->switch(
            $dictionary,
            $scenarioShips,
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

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $scenarioShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface[] $scenarioShips
     */
    private function detectSwitchType(array $scenarioShips, string $side): string
    {
        $fleetCrewLevel = $this->levelExperienceDetector->getFleetLevel($scenarioShips, $side);

        switch ($fleetCrewLevel) {
            case FsShip::LEVEL_ELITE:
            case FsShip::LEVEL_VETERAN:
                return SwitcherInterface::SWITCH_CLASS;
            default:
                return SwitcherInterface::SWITCH_WITH_ERROR;
        }
    }

    private function backupFsScenario(): void
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
