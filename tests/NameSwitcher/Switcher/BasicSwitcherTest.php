<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       08/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\NameSwitcher\Switcher;

use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\Core\Tas\Scenario\Scenario;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;
use App\NameSwitcher\Switcher\BasicSwitcher;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class BasicSwitcherTest extends TestCase
{
    public function testSwitch(): void
    {
        $scenario = $this->getMockBuilder(Scenario::class)->disableOriginalConstructor()->getMock();
        $scenario->method('getFsShips')->will($this->returnValue(
           [
               'Hood' => new FsShip(['NAME' => 'Hood', 'SHORTNAME' => 'Hood', 'TYPE' => 'BC', 'CLASS' => 'Hood']),
               'Hindenburg' => new FsShip(['NAME' => 'Hindenburg', 'SHORTNAME' => 'Hindenburg', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']),
               'Dido' => new FsShip(['NAME' => 'Dido', 'SHORTNAME' => 'Dido', 'TYPE' => 'CL', 'CLASS' => 'Dido']),
           ]
        ));

        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dico->method('getShipsList')->will($this->returnValue(
            [
                'Hindenburg' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => '',
                    'TasName' => '',
                    'FsClass' => '',
                    'FsName' => 'Bismarck',
                    'FsShortName' => 'Hindenburg',
                    'SimilarTo' => null,
                ]),
                'Hood' => new DictionaryShip([
                    'Type' => 'BC',
                    'Class' => '',
                    'TasName' => '',
                    'FsClass' => '',
                    'FsName' => 'Hood',
                    'FsShortName' => 'Hood',
                    'SimilarTo' => null,
                ]),
                'Dido' => new DictionaryShip([
                    'Type' => 'CL',
                    'Class' => '',
                    'TasName' => '',
                    'FsClass' => '',
                    'FsName' => 'Dido',
                    'FsShortName' => 'Dido',
                    'SimilarTo' => null,
                ]),
            ]
        ));

        $expected = [
            new Ship('Hood', 'Hood', 'Hood'),
            new Ship('Hindenburg', 'Bismarck', 'Hindenburg'),
            new Ship('Dido', 'Dido', 'Dido'),
        ];

        $switcher = new BasicSwitcher();
        $correspondence = $switcher->switch($dico, $scenario, 'red');

        static::assertEquals($expected, $correspondence);
    }
}
