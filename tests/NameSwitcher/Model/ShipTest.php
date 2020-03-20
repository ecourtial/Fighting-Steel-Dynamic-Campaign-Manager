<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\NameSwitcher\Model;

use App\Core\Exception\InvalidInputException;
use App\NameSwitcher\Exception\InvalidShipDataException;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Model\Ship;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    protected const CSV_DATA = [
        'Type' => 'BB',
        'Class' => 'Richelieu',
        'TasName' => 'Clemenceau',
        'FsClass' => 'Richelieu',
        'FsName' => 'Richelieu',
        'FsShortName' => 'Clemenceau',
        'SimilarTo' => 'Dunkerque|Nelson',
    ];

    public function testHydration(): void
    {
        $ship = new Ship(static::CSV_DATA);

        // Test basic getters and setters
        foreach (static::CSV_DATA as $key => $value) {
            if ('SimilarTo' !== $key) { // Has a specific code
                $methodName = 'get' . ucfirst($key);
                static::assertEquals($value, $ship->$methodName());
            }
        }
    }

    public function testMatchCriteria(): void
    {
        $ship = new Ship(static::CSV_DATA);
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau']));
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau', 'SimilarTo' => 'Nelson']));
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau', 'FsName' => 'Richelieu']));
    }

    public function testDoesNotMatchCriteria(): void
    {
        $ship = new Ship(static::CSV_DATA);
        static::assertFalse($ship->matchCriteria(['DummyParam' => 'Clemenceau']));
        static::assertFalse($ship->matchCriteria(['TasName' => 'Clemenceau', 'FsName' => 'Roma']));
        static::assertFalse($ship->matchCriteria(['Type' => 'BB', 'TasName' => 'Clemenceau', 'FsName' => 'Roma']));
        static::assertFalse($ship->matchCriteria(['Type' => 'BB', 'TasName' => 'Clemenceau', 'SimilarTo' => 'Roma']));
    }

    public function testRandomSimilarShip(): void
    {
        $ship = new Ship(static::CSV_DATA);

        // Test success
        $similarShip = $ship->getRandomSimilarShip();
        if ('Nelson' !== $similarShip && 'Dunkerque' !== $similarShip) {
            static::fail('Fail test get random similar ship');
        }

        // Test randomness control
        $ship->setSimilarTo(null);
        $ship->setSimilarTo('Nelson');
        static::assertEquals('Nelson', $ship->getRandomSimilarShip());

        // Test fails
        $ship->setSimilarTo(null);
        try {
            $ship->getRandomSimilarShip();
            static::fail('Since there is no similar ships, an exception was expected.');
        } catch (NoShipException $exception) {
            static::assertEquals('No similar ship found for BB Clemenceau', $exception->getMessage());
        }
    }

    public function testInvalidInputField(): void
    {
        $data = static::CSV_DATA;
        $data['Foo'] = ['Bar'];
        unset($data['TasName']);

        try {
            new Ship($data);
            static::fail('Since the input data is invalid, an exception was expected.');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals("The attribute 'Foo' is unknown", $exception->getMessage());
        }
    }

    public function testInvalidInputFieldQty(): void
    {
        $data = static::CSV_DATA;
        unset($data['TasName']);

        try {
            new Ship($data);
            static::fail('An exception was expected since the field qty is not the correct one');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals('Invalid ship attribute quantity', $exception->getMessage());
        }
    }

    public function testInvalidShortName(): void
    {
        $data = static::CSV_DATA;
        $data['FsShortName'] = '12345678900';

        try {
            new Ship($data);
            static::fail('Since the short name is too long, an exception was expected.');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals("FS Short name is too long: '12345678900'", $exception->getMessage());
        }
    }

    public function testBadType(): void
    {
        $data = static::CSV_DATA;
        $data['Type'] = 'AH';
        try {
            new Ship($data);
        } catch (InvalidInputException $exception) {
            static::assertEquals("Ship type 'AH' is unknown", $exception->getMessage());
        }
    }
}
