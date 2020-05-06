<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       08/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\NameSwitcher\Switcher;

use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Switcher\BasicSwitcher;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class BasicSwitcherTest extends TestCase
{
    public function testSwitch(): void
    {
        $ships = [
               'Hood42' => new FsShip(['NAME' => 'Hood42', 'SHORTNAME' => 'Hood42', 'TYPE' => 'BC', 'CLASS' => 'Hood']),
               'Hindenburg' => new FsShip(['NAME' => 'Hindenburg', 'SHORTNAME' => 'Hindenburg', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']),
               'Dido' => new FsShip(['NAME' => 'Dido', 'SHORTNAME' => 'Dido', 'TYPE' => 'CL', 'CLASS' => 'Dido']),
        ];

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
                'Hood42' => new DictionaryShip([
                    'Type' => 'BC',
                    'Class' => 'Hood',
                    'TasName' => 'Hood42',
                    'FsClass' => 'Hood',
                    'FsName' => 'Hood',
                    'FsShortName' => 'Hood42',
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
            'Hood42' => new Ship('Hood42', 'Hood', 'Hood42'),
            'Hindenburg' => new Ship('Hindenburg', 'Bismarck', 'Hindenburg'),
            'Dido' => new Ship('Dido', 'Dido', 'Dido'),
        ];

        $switcher = new BasicSwitcher();
        // The side is here for testing. It is supposed to be ignored in the basic Switcher.
        $correspondence = $switcher->switch($dico, $ships, 'Red');

        static::assertEquals($expected, $correspondence);
    }

    public function testShipIsNotInDictionary(): void
    {
        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dico->expects(static::once())->method('validateShipExistsInDictionary')->with('Dido')
            ->willThrowException(new NoShipException('Whatever'));

        $ships = [
            'Dido' => new FsShip(['NAME' => 'Dido', 'SHORTNAME' => 'Dido', 'TYPE' => 'CL', 'CLASS' => 'Dido']),
        ];

        $switcher = new BasicSwitcher();
        try {
            $switcher->switch($dico, $ships, 'Red');
            static::fail('Since the ship is not in the dictionary, an exception was expected');
        } catch (NoShipException $exception) {
            static::assertEquals('Whatever', $exception->getMessage());
        }
    }
}
