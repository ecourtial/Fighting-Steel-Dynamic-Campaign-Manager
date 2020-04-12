<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Tests\NameSwitcher\Transformer;

use App\Core\File\TextFileWriter;
use App\NameSwitcher\Transformer\CorrespondenceWriter;
use App\NameSwitcher\Transformer\Ship;
use PHPUnit\Framework\TestCase;

class CorrespondenceWriterTest extends TestCase
{
    public function testOutput(): void
    {
        $ships = [
            new Ship('Britannic', 'Titanic', 'Olympic3'),
            new Ship('Kentucky', 'Iowa', 'Iowa5'),
        ];

        (new CorrespondenceWriter(new TextFileWriter(), $_ENV['FS_LOCATION']))->output($ships);
        $outputFile = $_ENV['FS_LOCATION'] . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR . 'correspondence.ini';
        $lines = file_get_contents($outputFile);
        $lines = explode(PHP_EOL, $lines);
        unlink($outputFile);

        static::assertEquals(
            ['Britannic=Olympic3', 'Kentucky=Iowa5'],
            $lines
        );
    }
}
