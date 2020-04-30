<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\Core\File;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use PHPUnit\Framework\TestCase;

class IniFileReaderTest extends TestCase
{
    /** @var IniReader */
    private static $iniReader;

    public static function setUpBeforeClass(): void
    {
        $textFileReader = new TextFileReader();
        static::$iniReader = new IniReader($textFileReader);
    }

    public function testGetDataSuccess(): void
    {
        $data = [];
        foreach (static::$iniReader->getData('tests/Assets/dcm-config.ini') as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [
                ['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea'],
                ['key' => 'FS_PATH', 'value' => 'C:\Program Files\Fighting Steel'],
            ],
            $data
        );
    }

    public function testGetDataMalformedLine(): void
    {
        $fileName = 'tests/Assets/dcm-config-bad.ini';

        // Normal
        $data = [];
        foreach (static::$iniReader->getData($fileName) as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea']],
            $data
        );

        // Header
        $data = [];
        foreach (static::$iniReader->getData($fileName, false) as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [
                ['key' => 'header_1', 'value' => 'GENERAL CONFIG'],
                ['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea'],
            ],
            $data
        );

        // Malformed
        $data = [];
        foreach (static::$iniReader->getData($fileName, false, false) as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [
                ['key' => 'header_1', 'value' => 'GENERAL CONFIG'],
                ['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea'],
                ['key' => 'FS_PATH" C:\Program Files\Fighting Steel', 'value' => ''],
                ['key' => 'Pouet', 'value' => ''],
            ],
            $data
        );
    }

    public function testWithHeaders(): void
    {
        $data = [];
        foreach (static::$iniReader->getData('tests/Assets/dcm-config.ini', false) as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [
                ['key' => 'header_1', 'value' => 'GENERAL CONFIG - NOTE FILE HAS SPACES FOR TESTING PURPOSE'],
                ['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea'],
                ['key' => 'FS_PATH', 'value' => 'C:\Program Files\Fighting Steel'],
            ],
            $data
        );
    }
}
