<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       27/02/2020 (dd-mm-YYYY)
 */

namespace App\Core\File;

class IniReader
{
    /** @var \App\Core\File\TextFileReader */
    private $textFileReader;

    public function __construct(TextFileReader $textFileReader)
    {
        $this->textFileReader = $textFileReader;
    }

    /** @return string[] */
    public function getData(string $fileName): array
    {
        $data = $this->textFileReader->getFileContent($fileName);
        $parsedData = [];
        $lineCount = 0;

        foreach ($data as $line) {
            $keys = explode('=', $line);
            if (2 !== count($keys)) {
                throw new \RuntimeException("For line #{$lineCount} in file '{$fileName}': malformed line");
            }

            $parsedData[trim($keys[0])] = trim($keys[1]);
            $lineCount++;
        }

        return $parsedData;
    }
}
