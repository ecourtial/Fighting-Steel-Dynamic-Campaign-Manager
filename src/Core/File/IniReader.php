<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

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
    public function getData(string $fileName, bool $ignoreHeaders = true, bool $ignoreMalformed = true): \Generator
    {
        $headerCount = 0;

        foreach ($this->textFileReader->getFileContent($fileName) as $line) {
            // Ignore empty lines
            $line = trim($line, ' "');
            if ('' === $line) {
                continue;
            }

            if (preg_match('/^\[.*]$/', $line)) {
                // Ignore headers of sections?
                if ($ignoreHeaders) {
                    continue;
                } else {
                    $headerCount++;
                    yield [
                        'key' => 'header_' . $headerCount,
                        'value' => str_replace(['[', ']'], '', $line),
                    ];
                }
            } else {
                // Standard lines. Properly formed or not?
                $keys = explode('=', $line);

                if (2 !== count($keys)) {
                    if ($ignoreMalformed) {
                        continue;
                    } else {
                        yield ['key' => $line, 'value' => ''];
                    }
                } else {
                    yield [
                        'key' => trim($keys[0]),
                        'value' => trim($keys[1], ' "'),
                    ];
                }
            }
        }
    }
}
