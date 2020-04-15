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
use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Fs\Scenario\Ship\ShipExtractor;
use App\Core\Fs\Scenario\SideDetector;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryFactory;
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
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock
        ] = $this->getMocks();

        $scenarioPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $dest = $scenarioPath . 'A_TAS_Scenario.scn';

        $shipExtractorMock->expects($this->once())->method('extract')->with($dest, 'NIGHTTRAINING')->will($this->returnValue([]));

        $sideDetector->expects($this->once())->method('detectSide')->with(
            [],
            'Andrea Doria'
        )->willReturn('Blue');

        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dicoFactory->expects($this->once())->method('getDictionary')->with('Eric/dico.csv')
            ->willReturn($dico);

        $basicSwitch = $this->getMockBuilder($switcherClass)->getMock();
        $basicSwitch->expects($this->once())->method('switch')->with(
            $dico,
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
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock,
            $_ENV['FS_LOCATION']
        );

        copy(
            $scenarioPath . 'Sample' . DIRECTORY_SEPARATOR . 'TasBackup_20200406123456.scn',
            $dest
        );
        $scenarioProcessor->convertFromTasToFs('Eric/dico.csv', 'Andrea Doria', $level);
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
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock
        ] = $this->getMocks();

        $scenarioProcessor = new ScenarioProcessor(
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock,
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
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock
        ] = $this->getMocks();

        $fsScenarioFolder = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $filePath = $fsScenarioFolder . 'A_TAS_Scenario.scn';

        $iniReader->expects($this->once())->method('getData')->with($fsScenarioFolder . 'correspondence.ini')
            ->will($this->generate([['key' => 'NAME', 'value' => 'Foo']]));
        $scenarioUpdater->expects($this->once())->method('updateAfterFs')->with(['NAME' => 'Foo'], $filePath);

        $scenarioProcessor = new ScenarioProcessor(
            $switchFacto,
            $correspWriter,
            $dicoFactory,
            $scenarioUpdater,
            $fleetLevelDetector,
            $sideDetector,
            $iniReader,
            $shipExtractorMock,
            $_ENV['FS_LOCATION']
        );
        $scenarioProcessor->convertFromFsToTas();
    }

    private function getMocks(): array
    {
        return [
            $this->getMockBuilder(SwitcherFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(CorrespondenceWriter::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DictionaryFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ScenarioUpdater::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(FleetLevelExperienceDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(SideDetector::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(IniReader::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ShipExtractor::class)->disableOriginalConstructor()->getMock(),
        ];
    }
}
