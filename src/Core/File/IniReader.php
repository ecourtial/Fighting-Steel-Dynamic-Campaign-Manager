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

            $data = $this->handleLine($headerCount, $line, $ignoreHeaders, $ignoreMalformed);
            if (null !== $data) {
                yield $data;
            }
        }
    }

    /** @return string[]|null */
    private function handleLine(int &$headerCount, string $line, bool $ignoreHeaders, bool $ignoreMalformed): ?array
    {
        if (preg_match('/^\[.*]$/', $line)) {
            // Ignore headers of sections?
            if (false === $ignoreHeaders) {
                $headerCount++;

                return [
                    'key' => 'header_' . $headerCount,
                    'value' => str_replace(['[', ']'], '', $line),
                ];
            }
        } else {
            // Standard lines. Properly formed or not?
            $keys = explode('=', $line);

            if (2 !== count($keys)) {
                if (false === $ignoreMalformed) {
                    return ['key' => $line, 'value' => ''];
                }
            } else {
                return [
                    'key' => trim($keys[0]),
                    'value' => trim($keys[1], ' "'),
                ];
            }
        }

        return null;
    }
}
