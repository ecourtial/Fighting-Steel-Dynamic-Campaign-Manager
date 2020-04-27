<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       06/04/2020 (dd-mm-YYYY)
 */

namespace App\Core\Fs\Scenario;

use App\Core\File\TextFileReader;
use App\Core\File\TextFileWriter;

// Note : this class does not control if the quality of the content. It's job is just to replace it.
class ScenarioUpdater
{
    public const NAME_OCCURENCE = 'NAME=';
    public const SHORTNAME_OCCURENCE = 'SHORTNAME=';

    private TextFileReader $fileReader;
    private TextFileWriter $textFileWriter;

    public function __construct(TextFileReader $fileReader, TextFileWriter $textFileWriter)
    {
        $this->fileReader = $fileReader;
        $this->textFileWriter = $textFileWriter;
    }

    /** @param \App\NameSwitcher\Transformer\Ship[] $correspondence */
    public function updateBeforeFs(array $correspondence, string $fsScenarioPath): void
    {
        $newContent = [];
        $nameLine = null;
        $currentLine = null;
        $currentName = '';

        foreach ($this->fileReader->getFileContent($fsScenarioPath) as $element) {
            $nameIndex = strpos($element, static::NAME_OCCURENCE);
            $shortNameIndex = strpos($element, static::SHORTNAME_OCCURENCE);

            if (0 === $nameIndex) {
                $currentName = substr($element, 5);
                if (array_key_exists($currentName, $correspondence)) {
                    $nameLine = $currentLine;
                } else {
                    // The ship is on our side so we don't switch
                    $newContent[] = $element;
                }
            } elseif (0 === $shortNameIndex && $nameLine === ($currentLine - 1)) {
                $newContent[] = static::NAME_OCCURENCE . $correspondence[$currentName]->getName();
                $newContent[] = static::SHORTNAME_OCCURENCE . $correspondence[$currentName]->getShortName();
                $nameLine = null;
            } else {
                $newContent[] = $element;
            }

            $currentLine++;
        }

        $this->textFileWriter->writeMultilineFromArray($fsScenarioPath, $newContent);
    }

    /** @param string[] $correspondence */
    public function updateAfterFs(array $correspondence, string $fsScenarioPath): void
    {
        $newContent = [];
        $nameLine = null;
        $previousLineContent = null;
        $currentLine = null;

        foreach ($this->fileReader->getFileContent($fsScenarioPath) as $element) {
            $nameIndex = strpos($element, static::NAME_OCCURENCE);
            $shortNameIndex = strpos($element, static::SHORTNAME_OCCURENCE);

            if (0 === $nameIndex) {
                $nameLine = $currentLine;
                $previousLineContent = $element;
            } elseif (0 === $shortNameIndex && $nameLine === ($currentLine - 1)) {
                $shortName = substr($element, 10);
                if (array_key_exists($shortName, $correspondence)) {
                    $newContent[] = static::NAME_OCCURENCE . $correspondence[$shortName];
                } else {
                    // The ship was not switched when coming from TAS to FS
                    $newContent[] = $previousLineContent;
                }

                $newContent[] = $element;
            } else {
                $newContent[] = $element;
            }

            $currentLine++;
        }

        $this->textFileWriter->writeMultilineFromArray($fsScenarioPath, $newContent);
    }
}
