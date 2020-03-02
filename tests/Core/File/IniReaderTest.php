<?php

declare(strict_types=1);
/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       29/02/2020 (dd-mm-YYYY)
 */
use App\Core\Exception\SyntaxException;
use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use PHPUnit\Framework\TestCase;

class IniFileReaderTest extends TestCase
{
    public function testGetDataSuccess(): void
    {
        $fileReaderMock = $this->createMock(TextFileReader::class);
        $fileReaderMock->expects($this->once())
            ->method('getFileContent')
            ->will($this->returnValue(
                [
                    'TAS_PATH=C:\Program Files\Thunder At Sea',
                    'FS_PATH=C:\Program Files\Fighting Steel',
                ]
            ));

        $iniReader = new IniReader($fileReaderMock);
        $data = $iniReader->getData('tests/Assets/dcm-config.ini');

        static::assertEquals(
            [
                'TAS_PATH' => 'C:\Program Files\Thunder At Sea',
                'FS_PATH' => 'C:\Program Files\Fighting Steel',
            ],
            $data
        );
    }

    public function testGetDataMalformedLine(): void
    {
        $fileReaderMock = $this->createMock(TextFileReader::class);
        $fileReaderMock->expects($this->once())
            ->method('getFileContent')
            ->will($this->returnValue(
                [
                    'TAS_PATH=C:\Program Files\Thunder At Sea',
                    'FS_PATH C:\Program Files\Fighting Steel',
                ]
            ));

        $iniReader = new IniReader($fileReaderMock);
        $fileName = 'tests/Assets/dcm-config.ini';

        try {
            $iniReader->getData($fileName);
        } catch (SyntaxException $exception) {
            static::assertEquals(
                "For line #2 in file '{$fileName}': malformed line",
                $exception->getMessage()
            );
        }
    }
}
