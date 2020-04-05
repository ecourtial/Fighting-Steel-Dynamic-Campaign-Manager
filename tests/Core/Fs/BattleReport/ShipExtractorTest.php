<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\Fs\BattleReport;

use App\Core\Exception\FileNotFoundException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\BattleReport\Ship;
use App\Core\Fs\BattleReport\ShipExtractor;
use PHPUnit\Framework\TestCase;

class ShipExtractorTest extends TestCase
{
    protected static IniReader $iniReader;
    protected static ShipExtractor $shipExtractor;

    public static function setUpBeforeClass(): void
    {
        $textReader = new TextFileReader();
        static::$iniReader = new IniReader($textReader);
        static::$shipExtractor = new ShipExtractor(static::$iniReader, $_ENV['FS_LOCATION']);
    }

    public function testNormalExtraction(): void
    {
        $expected = [
            new Ship([
                'NAME' => 'ONSLOW',
                'SHORTNAME' => 'Onslow',
                'TYPE' => 'DD',
                'CLASS' => 'OP Class',
                'STATUS' => 'SHIP_SUNK',
            ]),
            new Ship([
                'NAME' => 'ORWELL',
                'SHORTNAME' => 'Orwell',
                'TYPE' => 'DD',
                'CLASS' => 'OP Class',
                'STATUS' => 'SHIP_SUNK',
            ]),
            new Ship([
                'NAME' => 'HIPPER',
                'SHORTNAME' => 'Hipper',
                'TYPE' => 'CA',
                'CLASS' => 'Hipper I',
                'STATUS' => 'SHIP_NORMAL',
            ]),
            new Ship([
                'NAME' => 'Z29',
                'SHORTNAME' => 'Z29',
                'TYPE' => 'DD',
                'CLASS' => '1936A Type',
                'STATUS' => 'SHIP_NORMAL',
            ]),
        ];

        static::assertEquals($expected, static::$shipExtractor->extract());
    }

    public function testFileDoesNotExist(): void
    {
        $realPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . '_End Of Engagement.sce';
        $badPath = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . '_End Of Engagement.sceZ';
        rename($realPath, $badPath);

        try {
            static::$shipExtractor->extract();
        } catch (FileNotFoundException $exception) {
            rename($badPath, $realPath);
            static::assertEquals(
                "Impossible to read the content of the file 'tests/Assets/FS/Scenarios/_End Of Engagement.sce'.",
                $exception->getMessage()
            );
        }
    }
}
