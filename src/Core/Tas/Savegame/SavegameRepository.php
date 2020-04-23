<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;

use App\Core\Exception\InvalidInputException;

class SavegameRepository
{
    private SavegameReader $savegameReader;
    private string $tasDirectory;

    public function __construct(SavegameReader $savegameReader, string $tasDirectory)
    {
        $this->savegameReader = $savegameReader;
        $this->tasDirectory = $tasDirectory;
    }

    /** @return string[] */
    public function getList(): array
    {
        /**
         * Will return an array of string for the available savegames, on the following pattern:
         * 'tasScenarioName' => 'saveX' where X is the number of the slot.
         */
        $saveGames = [];
        $folderContent = scandir($this->tasDirectory);

        foreach ($folderContent as $element) {
            $saveGameFullPath = $this->tasDirectory . DIRECTORY_SEPARATOR . $element;
            if (
                is_dir($saveGameFullPath)
                && preg_match(Savegame::PATH_REGEX, $element)
            ) {
                try {
                    $saveGame = $this->savegameReader->extract($saveGameFullPath);
                    $saveGames[$saveGame->getScenarioName()] = $element;
                } catch (\Throwable $exception) {
                    // Log
                }
            }
        }

        return $saveGames;
    }

    // By default return only the object with its metadata
    public function getOne(string $key, bool $fullData = false): Savegame
    {
        if (0 === preg_match(Savegame::PATH_REGEX, $key)) {
            throw new InvalidInputException("Savegame key '$key' is not a valid format");
        }

        $save = $this->savegameReader->extract($this->tasDirectory . DIRECTORY_SEPARATOR . $key);

        if ($fullData) {
        } else {
            return $save;
        }
    }
}
