<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\NameSwitcher;

use App\Core\Exception\CoreException;
use App\Core\File\IniReader;
use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Fs\Scenario\SideDetector;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\ScenarioProcessor;
use App\NameSwitcher\Switcher\BasicSwitcher;
use App\NameSwitcher\Switcher\ClassSwitcher;
use App\NameSwitcher\Switcher\ErrorSwitcher;
use App\NameSwitcher\Switcher\SwitcherFactory;
use App\NameSwitcher\Switcher\SwitcherInterface;
use App\NameSwitcher\Transformer\CorrespondenceWriter;
use App\Tests\GeneratorTrait;
use PHPUnit\Framework\TestCase;

class ScenarioProcessorTest extends TestCase
{
    use GeneratorTrait;

    private Dictionary $dico;
    private array $ships;

    public function setUp(): void
    {
        $this->dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $this->ships = [];
    }

    /** @dataProvider normalProvider */
    public function testConvertFromTasToFs(
        string $switcherClass,
        ?string $level = null,
        ?string $fleetLevel = null,
        ?string $expectedLevel = null
    ): void {
        [
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
        ] = $this->getMocks();

        $scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $dest = $scenarioPath . 'A_TAS_Scenario.scn';

        $sideDetector->expects($this->once())->method('detectSide')->with(
            [],
            'Andrea Doria'
        )->willReturn('Blue');

        $basicSwitch = $this->getMockBuilder($switcherClass)->getMock();
        $basicSwitch->expects($this->once())->method('switch')->with(
            $this->dico,
            [],
            'Blue'
        )->willReturn(['AH', 'HO']);

        if ($level) {
            $switchFacto->expects($this->once())->method('getSwitcher')->with($level)->will($this->returnValue($basicSwitch));
        } else {
            $fleetLevelDetector->expects($this->once())->method('getFleetLevel')->with([], 'Blue')->will($this->returnValue($fleetLevel));
            $switchFacto->expects($this->once())->method('getSwitcher')->with($expectedLevel)->will($this->returnValue($basicSwitch));
        }

        $correspWriter->expects($this->once())->method('output')->with(['AH', 'HO']);
        $scenarioUpdater->expects($this->once())->method('updateBeforeFs')->with(['AH', 'HO'], $dest);

        $scenarioProcessor = new ScenarioProcessor(
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader
        );

        copy(
            $scenarioPath . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn',
            $dest
        );

        $scenarioProcessor->convertFromTasToFs(
            'Andrea Doria',
            $this->dico,
            $this->ships,
            $scenarioPath,
            $dest,
            $level
        );
        unlink($dest);
    }

    public function normalProvider(): array
    {
        return [
            [BasicSwitcher::class, SwitcherInterface::SWITCH_BASIC],
            [ErrorSwitcher::class, null, Ship::LEVEL_GREEN, SwitcherInterface::SWITCH_WITH_ERROR],
            [ErrorSwitcher::class, null, Ship::LEVEL_AVERAGE, SwitcherInterface::SWITCH_WITH_ERROR],
            [ClassSwitcher::class, null, Ship::LEVEL_VETERAN, SwitcherInterface::SWITCH_CLASS],
            [ClassSwitcher::class, null, Ship::LEVEL_ELITE, SwitcherInterface::SWITCH_CLASS],
        ];
    }

    public function testBackupError(): void
    {
        [
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
        ] = $this->getMocks();

        $scenarioProcessor = new ScenarioProcessor(
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader
        );

        $scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $dest = $scenarioPath . 'A_TAS_Scenario.scn';

        try {
            $scenarioProcessor->convertFromTasToFs('ah', $this->dico, $this->ships, $scenarioPath, $dest);
            static::fail('Since the FS folder is a dummy one, an error was expected');
        } catch (CoreException $exception) {
            static::assertEquals(
                'Impossible to backup the FS scenario: copy(tests/Assets/FS/Scenarios/A_TAS_Scenario.scn): failed to open stream: No such file or directory',
                $exception->getMessage()
            );
        }
    }

    public function testConvertFromFsToTas(): void
    {
        [
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader
        ] = $this->getMocks();

        $fsScenarioFolder = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $filePath = $fsScenarioFolder . 'A_TAS_Scenario.scn';

        $iniReader->expects($this->once())->method('getData')->with($fsScenarioFolder . 'correspondence.ini')
            ->will($this->generate([['key' => 'NAME', 'value' => 'Foo']]));
        $scenarioUpdater->expects($this->once())->method('updateAfterFs')->with(['NAME' => 'Foo'], $filePath);

        $scenarioProcessor = new ScenarioProcessor(
            $switchFacto,
            $correspWriter,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader
        );

        $scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $dest = $scenarioPath . 'A_TAS_Scenario.scn';

        $scenarioProcessor->convertFromFsToTas($scenarioPath, $dest);
    }

    private function getMocks(): array
    {
        return [
            $this->getMockBuilder(SwitcherFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(CorrespondenceWriter::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ScenarioUpdater::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(FleetLevelExperienceDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SideDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(IniReader::class)->disableOriginalConstructor()->getMock(),
        ];
    }
}
