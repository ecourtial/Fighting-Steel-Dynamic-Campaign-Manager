<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame;


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
        return [];
    }

    public function getOne(string $key): Savegame
    {

    }

    private function checkEntry(): void
    {

    }
}
