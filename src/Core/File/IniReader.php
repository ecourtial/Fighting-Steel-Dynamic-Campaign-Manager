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
    public function getData(string $fileName, bool $ignoreHeaders = true): \Generator
    {
        $headerCount = 0;

        foreach ($this->textFileReader->getFileContent($fileName) as $line) {
            // Ignore headers of sections
            if (preg_match('/^\[.*]$/', $line)) {
                if ($ignoreHeaders) {
                    continue;
                } else {
                    $headerCount++;
                    yield [
                        'key' => trim('header_' . $headerCount),
                        'value' => str_replace(['[', ']'], '', $line),
                    ];
                }
            }

            $keys = explode('=', $line);
            if (2 !== count($keys)) {
                continue;
            }

            yield [
                'key' => trim($keys[0]),
                'value' => trim($keys[1], ' "'),
            ];
        }
    }
}
