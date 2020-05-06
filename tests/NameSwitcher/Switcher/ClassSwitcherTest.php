<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       08/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\NameSwitcher\Switcher;

use App\Core\Fs\FsShipInterface;
use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Switcher\ClassSwitcher;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class ClassSwitcherTest extends TestCase
{
    public function testNormalSwitch(): void
    {
        $ships = [
            'Richelieu' => (new FsShip(['NAME' => 'Richelieu', 'SHORTNAME' => 'Richelieu', 'TYPE' => 'BB', 'CLASS' => 'Richelieu']))->setSide('Blue'),
            'Clemenceau' => (new FsShip(['NAME' => 'Clemenceau', 'SHORTNAME' => 'Clemenceau', 'TYPE' => 'BB', 'CLASS' => 'Richelieu']))->setSide('Blue'),
            'Dido' => (new FsShip(['NAME' => 'Dido', 'SHORTNAME' => 'Dido', 'TYPE' => 'CL', 'CLASS' => 'Dido']))->setSide('Blue'),

            'Bismarck' => (new FsShip(['NAME' => 'Bismarck', 'SHORTNAME' => 'Bismarck', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']))->setSide('Red'),
            'Tirpitz' => (new FsShip(['NAME' => 'Tirpitz', 'SHORTNAME' => 'Tirpitz', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']))->setSide('Red'),
            'Admiral Scheer' => (new FsShip(['NAME' => 'Admiral Scheer', 'SHORTNAME' => 'Scheer', 'TYPE' => 'CA', 'CLASS' => 'Lutzow']))->setSide('Red'),
            'Queen Elizabeth' => (new FsShip(['NAME' => 'Queen Elizabeth', 'SHORTNAME' => 'Queen Eliz', 'TYPE' => 'BB', 'CLASS' => 'Queen Elizabeth']))->setSide('Red'),
        ];

        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dico->method('getShipsList')->will($this->returnValue(
            [
                'Richelieu' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => 'Richelieu',
                    'TasName' => 'Richelieu',
                    'FsClass' => 'Richelieu',
                    'FsName' => 'Richelieu',
                    'FsShortName' => 'Richelieu',
                    'SimilarTo' => null,
                ]),
                'Clemenceau' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => 'Richelieu',
                    'TasName' => 'Clemenceau',
                    'FsClass' => 'Richelieu',
                    'FsName' => 'Richelieu',
                    'FsShortName' => 'Clemenceau',
                    'SimilarTo' => null,
                ]),
                'Bismarck' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => 'Bismarck',
                    'TasName' => 'Bismarck',
                    'FsClass' => 'Bismarck',
                    'FsName' => 'Bismarck',
                    'FsShortName' => 'Bismarck',
                    'SimilarTo' => null,
                ]),
                'Tirpitz' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => 'Tirpitz',
                    'TasName' => 'Tirpitz',
                    'FsClass' => 'Tirpitz',
                    'FsName' => 'Tirpitz',
                    'FsShortName' => 'Tirpitz',
                    'SimilarTo' => null,
                ]),
                'Admiral Scheer' => new DictionaryShip([
                    'Type' => 'CA',
                    'Class' => 'Lutzow',
                    'TasName' => 'Admiral Scheer',
                    'FsClass' => 'Lutzow',
                    'FsName' => 'Admiral Scheer',
                    'FsShortName' => 'Scheer',
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
                'Queen Elizabeth' => new DictionaryShip([
                    'Type' => 'BB',
                    'Class' => 'Queen Eliz',
                    'TasName' => 'Queen Elizabeth',
                    'FsClass' => 'Queen Elizabeth',
                    'FsName' => 'Queen Elizabeth',
                    'FsShortName' => 'Queen Eliz',
                    'SimilarTo' => null,
                ]),
            ]
        ));

        $expected = [
            'Richelieu' => new Ship('Richelieu', 'Richelieu', 'Richelieu'),
            'Clemenceau' => new Ship('Clemenceau', 'Richelieu', 'Clemenceau'),
            'Dido' => new Ship('Dido', 'Dido', 'Dido'),
            'Bismarck' => new Ship('Bismarck', 'Bismarck', 'Bismarc#1'),
            'Tirpitz' => new Ship('Tirpitz', 'Tirpitz', 'Bismarc#2'),
            'Admiral Scheer' => new Ship('Admiral Scheer', 'Admiral Scheer', 'Lutzow#1'),
            'Queen Elizabeth' => new Ship('Queen Elizabeth', 'Queen Elizabeth', 'Queen E#1'),
        ];

        $switcher = new ClassSwitcher();
        $correspondence = $switcher->switch($dico, $ships, 'Blue');

        static::assertEquals($expected, $correspondence);

        // This small one is a workaround (proposed by the infection dev team) to circumvent an infection limit.
        $classTest = new class() extends ClassSwitcher {
            protected function addNewCorrespondence(FsShipInterface $fsShip, Dictionary $dictionary): Ship
            {
                parent::initialize();

                return parent::addNewCorrespondence($fsShip, $dictionary);
            }

            public function workaround(FsShipInterface $fsShip, Dictionary $dictionary): void
            {
                $this->addNewCorrespondence($fsShip, $dictionary);
            }
        };

        $classTest->workaround(
            new FsShip(['NAME' => 'Richelieu', 'SHORTNAME' => 'Richelieu', 'TYPE' => 'BB', 'CLASS' => 'Richelieu']),
            $dico
        );
    }

    public function testShipIsNotInDictionary(): void
    {
        $dico = $this->getMockBuilder(Dictionary::class)->disableOriginalConstructor()->getMock();
        $dico->expects(static::once())->method('validateShipExistsInDictionary')->with('Richelieu')
            ->willThrowException(new NoShipException('Whatever'));

        $ships = [
            'Richelieu' => (new FsShip(['NAME' => 'Richelieu', 'SHORTNAME' => 'Richelieu', 'TYPE' => 'BB', 'CLASS' => 'Richelieu']))->setSide('Blue'),
        ];

        $switcher = new ClassSwitcher();
        try {
            $switcher->switch($dico, $ships, 'Red');
            static::fail('Since the ship is not in the dictionary, an exception was expected');
        } catch (NoShipException $exception) {
            static::assertEquals('Whatever', $exception->getMessage());
        }
    }
}
