<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\File;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use PHPUnit\Framework\TestCase;

class IniFileReaderTest extends TestCase
{
    public function testGetDataSuccess(): void
    {
        $textFileReader = new TextFileReader();
        $iniReader = new IniReader($textFileReader);

        $data = [];
        foreach ($iniReader->getData('tests/Assets/dcm-config.ini') as $line) {
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
        $textFileReader = new TextFileReader();
        $iniReader = new IniReader($textFileReader);
        $fileName = 'tests/Assets/dcm-config-bad.ini';
        $data = [];

        foreach ($iniReader->getData($fileName) as $line) {
            $data[] = $line;
        }

        static::assertEquals(
            [['key' => 'TAS_PATH', 'value' => 'C:\Program Files\Thunder At Sea']],
            $data
        );
    }
}
