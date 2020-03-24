<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Core\File;

use App\Core\Exception\FileNotFoundException;
use App\Core\File\TextFileReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

class TextFileReaderTest extends TestCase
{
    public function testGetDataSuccess(): void
    {
        $fileReader = new TextFileReader();
        $data = [];
        foreach ($fileReader->getFileContent('tests/Assets/dcm-config.ini') as $line) {
            $data[] = $line;
        }

        static::assertEquals(
           [
               '[GENERAL CONFIG - NOTE FILE HAS SPACES FOR TESTING PURPOSE]',
               'TAS_PATH = "C:\Program Files\Thunder At Sea"',
               'FS_PATH=" C:\Program Files\Fighting Steel"',
           ],
            $data
        );
    }

    public function testGetDataFileDoesNotExist(): void
    {
        $fileReader = new TextFileReader();
        $file = 'tests/Assets/dcm-config.iniZ';

        try {
            foreach ($fileReader->getFileContent($file) as $line) {
            }
            static::fail('An exception was expected since the file does not exist!');
        } catch (FileNotFoundException $ex) {
            static::assertEquals(
                "Impossible to read the content of the file '$file'.",
                $ex->getMessage()
            );
            static::assertEquals(0, $ex->getCode());
            static::assertNull($ex->getPrevious());
        }
    }

    public function testImpossibleToClose(): void
    {
        $fileReader = new TextFileReader();

        try {
            $data = [];
            foreach ($fileReader->getFileContent('tests/Assets/dcm-config.ini', true) as $line) {
                $data[] = $line;
            }
            static::fail('An exception was expected since the could not be closed!');
        } catch (IOException $ex) {
            static::assertEquals(
                "Impossible to close the file 'tests/Assets/dcm-config.ini'",
                $ex->getMessage()
            );
        }
    }
}
