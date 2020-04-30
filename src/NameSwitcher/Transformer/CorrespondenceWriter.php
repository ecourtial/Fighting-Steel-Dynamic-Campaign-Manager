<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

declare(strict_types=1);

namespace App\NameSwitcher\Transformer;

use App\Core\File\TextFileWriter;

class CorrespondenceWriter
{
    private TextFileWriter $textFileWriter;
    private string $filePath;

    public function __construct(TextFileWriter $textFileWriter, string $fsDirectory)
    {
        $this->textFileWriter = $textFileWriter;
        $this->filePath = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios'
            . DIRECTORY_SEPARATOR . 'correspondence.ini';
    }

    /** @param \App\NameSwitcher\Transformer\Ship[] $correspondence */
    public function output(array $correspondence): void
    {
        $entries = [];
        foreach ($correspondence as $ship) {
            $entries[] = $ship->getOriginalName() . '=' . $ship->getShortName();
        }

        $this->textFileWriter->writeMultilineFromArray($this->filePath, $entries);
    }
}
