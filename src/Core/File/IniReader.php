<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace App\Core\File;

class IniReader
{
    protected TextFileReader $textFileReader;

    public function __construct(TextFileReader $textFileReader)
    {
        $this->textFileReader = $textFileReader;
    }

    /**
     * @return \Generator<array>
     *
     * @throws \App\Core\Exception\FileNotFoundException
     */
    public function getData(string $fileName): \Generator
    {
        $lineCount = 1;

        foreach ($this->textFileReader->getFileContent($fileName) as $line) {
            // Ignore headers of sections
            if (preg_match('/^\[.*]$/', $line)) {
                continue;
            }

            $keys = explode('=', $line);
            if (2 !== count($keys)) {
                continue;
            }
            $lineCount++;

            yield [
                'key' => trim($keys[0]),
                'value' => trim($keys[1], ' "'),
            ];
        }
    }
}
