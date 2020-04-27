<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\Core\File;

use App\Core\File\TextFileWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

class TextFileWriterTest extends TestCase
{
    public function testNormalMultiline(): void
    {
        $path = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'testOutput.txt';
        $lines = ['Bonjour', 'Eric'];
        (new TextFileWriter())->writeMultilineFromArray($path, $lines);
        $content = file_get_contents($path);
        $content = explode(PHP_EOL, $content);
        unlink($path);
        static::assertEquals($lines, $content);
    }

    public function testError(): void
    {
        try {
            (new TextFileWriter())->writeMultilineFromArray('AH.txt', [], true);
            static::fail('Since the output failed, an exception was expected.');
        } catch (IOException $exception) {
            static::assertEquals(
                "Impossible to update the content of the file 'AH.txt'",
                $exception->getMessage()
            );
        }
    }
}
