<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       27/02/2020 (dd-mm-YYYY)
 */

namespace App\Core\File;

use App\Core\Exception\FileNotFoundException;

class TextFileReader
{
    /**
     * @return string[]
     *
     * @throws \App\Core\Exception\FileNotFoundException
     */
    public function getFileContent(string $filename): array
    {
        $content = [];
        $handle = @fopen($filename, 'r');

        if ($handle) {
            while (false !== ($buffer = fgets($handle, 4096))) {
                $content[] = trim($buffer);
            }
            fclose($handle);
        } else {
            throw new FileNotFoundException("Impossible to read the content of the file '{$filename}'.");
        }

        return $content;
    }
}
