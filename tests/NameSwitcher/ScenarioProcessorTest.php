<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\NameSwitcher;

use App\Core\Exception\CoreException;
use App\Core\File\IniReader;
use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\ScenarioUpdater;
use App\Core\Fs\Scenario\SideDetector;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\ScenarioProcessor;
use App\NameSwitcher\Switcher\BasicSwitcher;
use App\NameSwitcher\Switcher\SwitcherFactory;
use App\NameSwitcher\Switcher\SwitcherInterface;
use App\NameSwitcher\Transformer\CorrespondenceWriter;
use App\Tests\GeneratorTrait;
use PHPUnit\Framework\TestCase;

class ScenarioProcessorTest extends TestCase
{
    use GeneratorTrait;

    public function testConvertFromTasToFs(): void
    {
        [
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader
        ] = $this->getMocks();

        $scenario = $this->getMockBuilder(Scenario::class)->disableOriginalConstructor()->getMock();
        $scenario->expects($this->once())->method('getFullPath')->will($this->returnValue('EH'));

        $scenarioRepo->expects($this->once())->method('getOneWillAllData')->with('Eric')->will($this->returnValue($scenario));

        $filePath = 'tests/Assets/FS' . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'A_TAS_Scenario.scn';
        $sideDetector->expects($this->once())->method('detectSide')->with(
            $filePath,
            'Andrea Doria'
        )->willReturn('Blue');

        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dicoFactory->expects($this->once())->method('getDictionary')->with('EH' . DIRECTORY_SEPARATOR . 'dictionary.csv')
            ->willReturn($dico);

        $basicSwitch = $this->getMockBuilder(BasicSwitcher::class)->getMock();
        $basicSwitch->expects($this->once())->method('switch')->with(
            $dico,
            $scenario,
            'Blue'
        )->willReturn(['AH', 'HO']);
        $switchFacto->expects($this->once())->method('getSwitcher')->with(SwitcherInterface::SWITCH_BASIC)->will($this->returnValue($basicSwitch));

        $correspWriter->expects($this->once())->method('output')->with(['AH', 'HO']);
        $scenarioUpdater->expects($this->once())->method('updateBeforeFs')->with(['AH', 'HO'], $filePath);

        $scenarioProcessor = new ScenarioProcessor(
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader,
            $_ENV['FS_LOCATION']
        );

        $scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $dest = $scenarioPath . 'A_TAS_Scenario.scn';
        copy(
            $scenarioPath . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn',
            $dest
        );
        $scenarioProcessor->convertFromTasToFs('Eric', 'Andrea Doria', SwitcherInterface::SWITCH_BASIC);
        unlink($dest);
    }

    public function testBackupError(): void
    {
        [
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader
        ] = $this->getMocks();

        $scenarioProcessor = new ScenarioProcessor(
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader,
            $_ENV['FS_LOCATION']
        );

        try {
            $scenarioProcessor->convertFromTasToFs('ah', 'oh');
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
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader
        ] = $this->getMocks();

        $fsScenarioFolder = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $filePath = $fsScenarioFolder . 'A_TAS_Scenario.scn';

        $iniReader->expects($this->once())->method('getData')->with($fsScenarioFolder . 'correspondence.ini')
            ->will($this->generate([['key' => 'NAME', 'value' => 'Foo']]));
        $scenarioUpdater->expects($this->once())->method('updateAfterFs')->with(['NAME' => 'Foo'], $filePath);

        $scenarioProcessor = new ScenarioProcessor(
            $scenarioRepo,
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDectector,
            $sideDetector,
            $iniReader,
            $_ENV['FS_LOCATION']
        );
        $scenarioProcessor->convertFromFsToTas();
    }

    private function getMocks(): array
    {
        return [
            $this->getMockBuilder(ScenarioRepository::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SwitcherFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(CorrespondenceWriter::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DictionaryFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ScenarioUpdater::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(FleetLevelExperienceDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SideDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(IniReader::class)->disableOriginalConstructor()->getMock(),
        ];
    }
}
