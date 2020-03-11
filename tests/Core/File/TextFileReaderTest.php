<?php

declare(strict_types=1);
/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       29/02/2020 (dd-mm-YYYY)
 */
use App\Core\Exception\FileNotFoundException;
use App\Core\File\TextFileReader;
use PHPUnit\Framework\TestCase;

class TextFileReaderTest extends TestCase
{
    public function testGetDataSuccess(): void
    {
        $fileReader = new TextFileReader();
        $data = $fileReader->getFileContent('tests/Assets/dcm-config.ini');

        static::assertEquals(
           [
               'TAS_PATH="C:\Program Files\Thunder At Sea"',
               'FS_PATH="C:\Program Files\Fighting Steel"',
           ],
            $data
        );
    }

    public function testGetDataFileDoesNotExist(): void
    {
        $fileReader = new TextFileReader();
        $file = 'tests/Assets/dcm-config.iniZ';

        try {
            $fileReader->getFileContent($file);
            static::fail('An exception was expected since the file does not exist!');
        } catch (FileNotFoundException $ex) {
            static::assertEquals(
                "Impossible to read the content of the file '$file'.",
                $ex->getMessage()
            );
        }
    }
}
