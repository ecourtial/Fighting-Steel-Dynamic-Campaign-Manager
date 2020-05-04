<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\NameSwitcher;

use App\Core\Fs\Scenario\Ship\ShipExtractor;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\ScenarioManager;
use App\NameSwitcher\ScenarioProcessor;
use PHPUnit\Framework\TestCase;

class ScenarioManagerTest extends TestCase
{
    public function testFromTasToFs(): void
    {
        [
            $scenarioProcessor,
            $dicoFactory,
            $scenarioRepo,
            $shipExtractor,
            $fsDirectory,
            $folder,
            $path
        ] = $this->getMocks();

        $key = 'goeben';
        $oneShip = 'Hood';
        $dico = 'dico.csv';
        $ships = [];
        $level = 'Basic';

        $dummyScenar = $this->getMockBuilder(Scenario::class)->disableOriginalConstructor()->getMock();
        $dummyScenar->expects(static::once())->method('getDictionaryPath')->willReturn($dico);
        $dummyDico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dicoFactory->expects(static::once())->method('getDictionary')->with($dico)->willReturn($dummyDico);
        $scenarioRepo->expects(static::once())->method('getOneWillAllData')->with($key)->willReturn($dummyScenar);
        $shipExtractor->expects(static::once())->method('extract')->with($path, 'NIGHTTRAINING')->willReturn($ships);

        $scenarioProcessor->expects(static::once())->method('convertFromTasToFs')->with(
            $oneShip,
            $dummyDico,
            $ships,
            $folder,
            $path,
            $level
        );

        $manager = new ScenarioManager($scenarioProcessor, $dicoFactory, $scenarioRepo, $shipExtractor, $fsDirectory);
        $manager->fromTasToFs($key, $oneShip, $level);
    }

    public function testFromFsToTas(): void
    {
        [
            $scenarioProcessor,
            $dicoFactory,
            $scenarioRepo,
            $shipExtractor,
            $fsDirectory,
            $folder,
            $path
        ] = $this->getMocks();

        $scenarioProcessor->expects(static::once())->method('convertFromFsToTas')->with($folder, $path);
        $manager = new ScenarioManager($scenarioProcessor, $dicoFactory, $scenarioRepo, $shipExtractor, $fsDirectory);
        $manager->fromFsToTas();
    }

    private function getMocks(): array
    {
        $scenariosFolder = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;

        return [
            $this->getMockBuilder(ScenarioProcessor::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(DictionaryFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ScenarioRepository::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ShipExtractor::class)->disableOriginalConstructor()->getMock(),
            $_ENV['FS_LOCATION'],
            $scenariosFolder,
            $scenariosFolder . 'A_TAS_Scenario.scn',
        ];
    }
}
