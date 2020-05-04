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

    public function __construct(
        SwitcherFactory $switcherFactory,
        CorrespondenceWriter $correspondenceWriter,
        ScenarioUpdater $scenarioUpdater,
        FleetLevelExperienceDetector $levelExperienceDetector,
        SideDetector $sideDetector,
        IniReader $iniReader
    ) {
        $this->switcherFactory = $switcherFactory;
        $this->correspondenceWriter = $correspondenceWriter;
        $this->scenarioUpdater = $scenarioUpdater;
        $this->levelExperienceDetector = $levelExperienceDetector;
        $this->iniReader = $iniReader;
        $this->sideDetector = $sideDetector;
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
        string $fsScenariosFolder,
        string $fsScenarioPath,
        ?string $switchLevel = null
    ): void {
        $this->backupFsScenario($fsScenariosFolder, $fsScenarioPath);
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
        $this->scenarioUpdater->updateBeforeFs($correspondence, $fsScenarioPath);
    }

    public function convertFromFsToTas(string $fsScenariosFolder, string $fsScenarioPath): void
    {
        $content = [];
        foreach ($this->iniReader->getData($fsScenariosFolder . 'correspondence.ini') as $entry) {
            $content[$entry['key']] = $entry['value'];
        }
        $this->scenarioUpdater->updateAfterFs($content, $fsScenarioPath);
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

    private function backupFsScenario(string $fsScenariosFolder, string $fsScenarioPath): void
    {
        $date = (new \DateTime())->format('Y-m-d-H-i-s');
        $dest = $fsScenariosFolder . 'Backup' . DIRECTORY_SEPARATOR . $date . '.scn.bak';

        try {
            copy($fsScenarioPath, $dest);
        } catch (\Throwable $exception) {
            throw new CoreException('Impossible to backup the FS scenario: ' . $exception->getMessage());
        }
    }
}
