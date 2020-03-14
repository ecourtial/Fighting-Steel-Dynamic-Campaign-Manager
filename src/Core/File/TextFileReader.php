<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       27/02/2020 (dd-mm-YYYY)
 *
 * Warning: this class is "full" of "hacks to workaround
 * native functions PHP issues.
 */

namespace App\Core\File;

use App\Core\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

function fclose($resource, bool $test = false): bool
{
    if ($test) {
        return false;
    }

    return \fclose($resource);
}

class TextFileReader
{
    /** @var resource|null */
    protected $handle;

    /**
     * @return string[]
     *
     * @throws \App\Core\Exception\FileNotFoundException
     */
    public function getFileContent(string $filename, bool $test = false): array
    {
        $content = [];
        $this->openFile($filename);

        while (false !== ($buffer = fgets($this->handle))) {
            $content[] = trim($buffer);
        }

        if (false === fclose($this->handle, $test)) {
            throw new IOException("Impossible to close the file '{$filename}'");
        }

        return $content;
    }

    protected function openFile(string $filename): void
    {
        $this->handle = @fopen($filename, 'r');

        if (false === is_resource($this->handle)) {
            throw new FileNotFoundException("Impossible to read the content of the file '{$filename}'.");
        }
    }
}
