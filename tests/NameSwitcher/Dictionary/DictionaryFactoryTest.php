<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\NameSwitcher\Dictionary;

use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\Dictionary\DictionaryReader;
use App\NameSwitcher\Dictionary\Ship;
use App\Tests\GeneratorTrait;
use PHPUnit\Framework\TestCase;

class DictionaryFactoryTest extends TestCase
{
    use GeneratorTrait;

    public function testNormalRead(): void
    {
        $shipData = [
            'Type' => 'DD',
            'Class' => 'Mogador',
            'TasName' => 'Mogador',
            'FsClass' => 'Mogador',
            'FsName' => 'Mogador',
            'FsShortName' => 'Mogador',
            'SimilarTo' => 'Le Terrible',
        ];

        $reader = $this->getMockBuilder(DictionaryReader::class)->disableOriginalConstructor()->getMock();
        $reader->method('extractData')
            ->with('AH.csv')
            ->will($this->generate([$shipData]
        ));

        $factory = new DictionaryFactory($reader);
        $result = ($factory->getDictionary('AH.csv')->getShipsList());
        $ship = new Ship($shipData);

        static::assertEquals(1, count($result));
        static::assertEquals($ship, $result['Mogador']);
    }
}
