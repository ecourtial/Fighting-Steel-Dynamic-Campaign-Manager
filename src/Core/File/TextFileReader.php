<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 *
 * Warning: this class is "full" of "hacks to workaround
 * native functions PHP issues.
 */

namespace App\Core\File;

use App\Core\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * This hack is here to be able to mock the fclose() native PHP function.
 *
 * @param mixed[] $resource
 */
function fclose(array $resource, bool $test = false): bool
{
    if ($test) {
        return false;
    }

    return \fclose($resource['resource']);
}

class TextFileReader
{
    /** @var resource|null */
    protected $handle;

    /**
     * @return \Generator<String>
     *
     * @throws \App\Core\Exception\FileNotFoundException
     */
    public function getFileContent(string $filename, bool $test = false): \Generator
    {
        $content = [];
        $this->openFile($filename);

        while (false !== ($buffer = fgets($this->handle))) {
            yield trim($buffer);
        }

        if (false === fclose(['resource' => $this->handle], $test)) {
            throw new IOException("Impossible to close the file '{$filename}'");
        }

        return $content;
    }

    private function openFile(string $filename): void
    {
        $this->handle = @fopen($filename, 'r');

        if (false === is_resource($this->handle)) {
            throw new FileNotFoundException($filename);
        }
    }
}
