<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Fs\Ship;

use App\Core\Fs\Ship\Ship;
use App\NameSwitcher\Exception\InvalidShipDataException;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    protected const INPUT_DATA = [
        'TYPE' => 'BB',
        'CLASS' => 'Richelieu',
        'NAME' => 'Clemenceau',
        'SHORTNAME' => 'Clemenceau',
    ];

    public function testHydration(): void
    {
        $ship = new Ship(static::INPUT_DATA);

        // Test basic getters and setters
        foreach (static::INPUT_DATA as $key => $value) {
            $methodName = 'get' . ucfirst($key);
            static::assertEquals($value, $ship->$methodName());
        }
    }

    public function testInvalidInputField(): void
    {
        $data = static::INPUT_DATA;
        $data['Foo'] = ['Bar'];
        unset($data['NAME']);

        try {
            new Ship($data);
            static::fail('Since the input data is invalid, an exception was expected.');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals("The attribute 'Foo' is unknown", $exception->getMessage());
        }
    }

    public function testInvalidInputFieldQty(): void
    {
        $data = static::INPUT_DATA;
        unset($data['NAME']);

        try {
            new Ship($data);
            static::fail('An exception was expected since the field qty is not the correct one');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals('Invalid ship attribute quantity', $exception->getMessage());
        }
    }

    public function testInvalidShortName(): void
    {
        $data = static::INPUT_DATA;
        $data['SHORTNAME'] = '12345678900';

        try {
            new Ship($data);
            static::fail('Since the short name is too long, an exception was expected.');
        } catch (InvalidShipDataException $exception) {
            static::assertEquals("FS Short name is too long: '12345678900'", $exception->getMessage());
        }
    }
}