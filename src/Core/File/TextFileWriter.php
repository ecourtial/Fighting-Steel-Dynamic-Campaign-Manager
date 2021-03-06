<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\Core\File;

use Symfony\Component\Filesystem\Exception\IOException;

// This hack is here to be able to mock the file_put_contents() native PHP function.
function file_put_contents(string $path, string $content, bool $test = false): bool
{
    if ($test) {
        return false;
    }

    return (bool) \file_put_contents($path, $content);
}

class TextFileWriter
{
    /** @param string[] $lines */
    public function writeMultilineFromArray(string $path, array $lines, bool $test = false): void
    {
        $data = implode(PHP_EOL, $lines);
        $this->writeMultilineFromString($path, $data, $test);
    }

    public function writeMultilineFromString(string $path, string $lines, bool $test = false): void
    {
        if (false === file_put_contents($path, $lines, $test)) {
            throw new IOException("Impossible to update the content of the file '{$path}'");
        }
    }
}
