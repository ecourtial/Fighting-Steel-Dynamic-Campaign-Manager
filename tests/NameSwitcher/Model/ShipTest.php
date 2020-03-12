<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       12/03/2020 (dd-mm-YYYY)
 */

use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Model\Ship;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    protected array $csvData = [
        'Type' => 'BB',
        'Class' => 'Richelieu',
        'TasName' => 'Clemenceau',
        'FsName' => 'Richelieu',
        'FsShortName' => 'Clemenceau',
        'SimilarTo' => 'Dunkerque|Nelson',
    ];

    public function testHydration(): void
    {
        $ship = new Ship($this->csvData);

        // Test basic getters and setters
        foreach ($this->csvData as $key => $value) {
            if ('SimilarTo' !== $key) { // Has a specific code
                $methodName = 'get' . ucfirst($key);
                static::assertEquals($value, $ship->$methodName());
            }
        }
    }

    public function testMatchCriteria(): void
    {
        $ship = new Ship($this->csvData);
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau']));
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau', 'SimilarTo' => 'Nelson']));
        static::assertTrue($ship->matchCriteria(['TasName' => 'Clemenceau', 'FsName' => 'Richelieu']));
    }

    public function testDoesNotMatchCriteria(): void
    {
        $ship = new Ship($this->csvData);
        static::assertFalse($ship->matchCriteria(['DummyParam' => 'Clemenceau']));
        static::assertFalse($ship->matchCriteria(['TasName' => 'Clemenceau', 'FsName' => 'Roma']));
        static::assertFalse($ship->matchCriteria(['Type' => 'BB', 'TasName' => 'Clemenceau', 'FsName' => 'Roma']));
        static::assertFalse($ship->matchCriteria(['Type' => 'BB', 'TasName' => 'Clemenceau', 'SimilarTo' => 'Roma']));
    }

    public function testRandomSimilarTo(): void
    {
        $ship = new Ship($this->csvData);

        // Test success
        $similarShip = $ship->getRandomSimilarShip();
        if ('Nelson' !== $similarShip && 'Dunkerque' !== $similarShip) {
            static::fail('Fail test get random similar ship');
        }

        // Test fails
        $ship->setSimilarTo(null);
        try {
            $ship->getRandomSimilarShip();
            static::fail('Since there is no similar ships, an exception was expected.');
        } catch (NoShipException $exception) {
            static::assertEquals('No similar ship found for BB Clemenceau', $exception->getMessage());
        }
    }
}
