<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       08/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\NameSwitcher\Switcher;

use App\Core\Fs\Scenario\Ship\Ship as FsShip;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Switcher\ErrorSwitcher;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class ErrorSwitcherTest extends TestCase
{
    public function testNormalSwitchWithSample(): void
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

        $dico = new Dictionary(
            [
                [
                    'Type' => 'BB',
                    'Class' => 'Richelieu',
                    'TasName' => 'Richelieu',
                    'FsClass' => 'Richelieu',
                    'FsName' => 'Richelieu',
                    'FsShortName' => 'Richelieu',
                    'SimilarTo' => 'Dunkerque|Nelson',
                ],
                [
                    'Type' => 'BB',
                    'Class' => 'Richelieu',
                    'TasName' => 'Clemenceau',
                    'FsClass' => 'Richelieu',
                    'FsName' => 'Richelieu',
                    'FsShortName' => 'Clemenceau',
                    'SimilarTo' => 'Dunkerque|Nelson',
                ],
                [
                    'Type' => 'BB',
                    'Class' => 'Bismarck',
                    'TasName' => 'Bismarck',
                    'FsClass' => 'Bismarck',
                    'FsName' => 'Bismarck',
                    'FsShortName' => 'Bismarck',
                    'SimilarTo' => 'Scharnhorst',
                ],
                [
                    'Type' => 'BB',
                    'Class' => 'Tirpitz',
                    'TasName' => 'Tirpitz',
                    'FsClass' => 'Tirpitz',
                    'FsName' => 'Tirpitz',
                    'FsShortName' => 'Tirpitz',
                    'SimilarTo' => 'Scharnhorst',
                ],
                [
                    'Type' => 'CA',
                    'Class' => 'Lutzow',
                    'TasName' => 'Admiral Scheer',
                    'FsClass' => 'Lutzow',
                    'FsName' => 'Admiral Scheer',
                    'FsShortName' => 'Scheer',
                    'SimilarTo' => 'Scharnhorst',
                ],
                [
                    'Type' => 'CL',
                    'Class' => 'Dido',
                    'TasName' => 'Dido',
                    'FsClass' => 'Dido',
                    'FsName' => 'Dido',
                    'FsShortName' => 'Dido',
                    'SimilarTo' => null,
                ],
                [
                    'Type' => 'BB',
                    'Class' => 'Queen Eliz',
                    'TasName' => 'Queen Elizabeth',
                    'FsClass' => 'Queen Elizabeth',
                    'FsName' => 'Queen Elizabeth',
                    'FsShortName' => 'Queen Eliz',
                    'SimilarTo' => 'Royal Sovereign',
                ],
            ]
        );

        $expected = [
            'Richelieu' => new Ship('Richelieu', 'Richelieu', 'Richelieu'),
            'Clemenceau' => new Ship('Clemenceau', 'Richelieu', 'Clemenceau'),
            'Dido' => new Ship('Dido', 'Dido', 'Dido'),
            'Bismarck' => new Ship('Bismarck', 'Bismarck', 'Scharnh#1'),
            'Tirpitz' => new Ship('Tirpitz', 'Tirpitz', 'Scharnh#2'),
            'Admiral Scheer' => new Ship('Admiral Scheer', 'Admiral Scheer', 'Scharnh#3'),
            'Queen Elizabeth' => new Ship('Queen Elizabeth', 'Queen Elizabeth', 'Royal S#1'),
        ];

        $switcher = new ErrorSwitcher();
        $correspondence = $switcher->switch($dico, $ships, 'Blue');

        foreach ($correspondence as $key => $ship) {
            static::assertEquals($expected[$key]->getName(), $ship->getName());
            static::assertEquals($expected[$key]->getOriginalName(), $ship->getOriginalName());
        }

        $this->assertVerif($correspondence);
    }

    public function testWithNoError(): void
    {
        $class = new class() extends ErrorSwitcher {
            public const MIN_THRESHOLD_ERROR_PROBABILITY = 1;
            public const MAX_THRESHOLD_ERROR_PROBABILITY = 1;
        };

        $ships = [
            'Bismarck' => (new FsShip(['NAME' => 'Bismarck', 'SHORTNAME' => 'Bismarck', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']))->setSide('Red'),
        ];
        $expected = [
            'Bismarck' => new Ship('Bismarck', 'Bismarck', 'Bismarc#1'),
        ];
        $dico = new Dictionary(
            [
                [
                    'Type' => 'BB',
                    'Class' => 'Bismarck',
                    'TasName' => 'Bismarck',
                    'FsClass' => 'Bismarck',
                    'FsName' => 'Bismarck',
                    'FsShortName' => 'Bismarck',
                    'SimilarTo' => 'Scharnhorst',
                ],
            ]
        );

        $switcher = new $class();
        $correspondence = $switcher->switch($dico, $ships, 'Blue');
        static::assertEquals($expected, $correspondence);
    }

    public function testWithError(): void
    {
        $class = new class() extends ErrorSwitcher {
            public const NO_ERROR_PROBABILITY_VALUE = 5;
        };

        $ships = [
            'Bismarck' => (new FsShip(['NAME' => 'Bismarck', 'SHORTNAME' => 'Bismarck', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']))->setSide('Red'),
            'Tirpitz' => (new FsShip(['NAME' => 'Tirpitz', 'SHORTNAME' => 'Tirpitz', 'TYPE' => 'BB', 'CLASS' => 'Bismarck']))->setSide('Red'),
            'Admiral Scheer' => (new FsShip(['NAME' => 'Admiral Scheer', 'SHORTNAME' => 'Scheer', 'TYPE' => 'CA', 'CLASS' => 'Lutzow']))->setSide('Red'),
        ];
        $expected = [
            'Bismarck' => new Ship('Bismarck', 'Bismarck', 'Scharnh#1'),
            'Tirpitz' => new Ship('Tirpitz', 'Tirpitz', 'Scharnh#2'),
            'Admiral Scheer' => new Ship('Admiral Scheer', 'Admiral Scheer', 'Scharnh#3'),
        ];
        $dico = new Dictionary(
            [
                [
                    'Type' => 'BB',
                    'Class' => 'Bismarck',
                    'TasName' => 'Bismarck',
                    'FsClass' => 'Bismarck',
                    'FsName' => 'Bismarck',
                    'FsShortName' => 'Bismarck',
                    'SimilarTo' => 'Scharnhorst',
                ],
                [
                    'Type' => 'BB',
                    'Class' => 'Tirpitz',
                    'TasName' => 'Tirpitz',
                    'FsClass' => 'Bismarck',
                    'FsName' => 'Tirpitz',
                    'FsShortName' => 'Tirpitz',
                    'SimilarTo' => 'Scharnhorst',
                ],
                [
                    'Type' => 'CA',
                    'Class' => 'Lutzow',
                    'TasName' => 'Admiral Scheer',
                    'FsClass' => 'Lutzow',
                    'FsName' => 'Admiral Scheer',
                    'FsShortName' => 'Scheer',
                    'SimilarTo' => 'Scharnhorst',
                ],
            ]
        );

        $switcher = new $class();
        $correspondence = $switcher->switch($dico, $ships, 'Blue');
        static::assertEquals($expected, $correspondence);
    }

    /** @param \App\NameSwitcher\Transformer\Ship[] $correspondence */
    private function assertVerif(array $correspondence): void
    {
        $alreadyMet = [];
        $possibilities = [
          'Bismarck' => ['Bismarc#1', 'Bismarc#2', 'Scharnh#1', 'Scharnh#2', 'Scharnh#3'],
          'Tirpitz' => ['Bismarc#1', 'Bismarc#2', 'Scharnh#1', 'Scharnh#2', 'Scharnh#3'],
          'Admiral Scheer' => ['Lutzow#1', 'Scharnh#1', 'Scharnh#2', 'Scharnh#3'],
          'Queen Elizabeth' => ['Queen E#1', 'Royal S#1'],
        ];

        foreach ($correspondence as $ship) {
            if (array_key_exists($ship->getOriginalName(), $possibilities)) {
                $shortname = $ship->getShortName();
                if (false === in_array($shortname, $possibilities[$ship->getOriginalName()], true)) {
                    static::fail("For the ship '{$ship->getOriginalName()}', the shortname '$shortname' was not one of the possibilities.");
                }

                if (true === in_array($shortname, $alreadyMet, true)) {
                    static::fail("For the ship '{$ship->getOriginalName()}', the shortname '$shortname' was already met.");
                }

                $alreadyMet[] = $shortname;
            }
        }
    }
}
