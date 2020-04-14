<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

use App\Core\Fs\FsShipInterface;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Transformer\Ship;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;

class ErrorSwitcher extends ClassSwitcher
{
    /** @var string[] */
    private array $classCorrespondence;

    protected function initialize(): void
    {
        parent::initialize();
        $this->classCorrespondence = [];
    }

    /**
     * Is actually \App\Core\Fs\Scenario\Ship\Ship[] $fsShips
     * but PHPStan has issue with interpreting interfaces
     *
     * @param \App\Core\Fs\FsShipInterface $fsShip
     */
    protected function addNewCorrespondence(FsShipInterface $fsShip, Dictionary $dictionary): Ship
    {
        $class = $fsShip->getClass();
        $currentName = $fsShip->getName();
        $dictionaryShip = $dictionary->findOneShip(['TasName' => $currentName]);


        if (true === array_key_exists($class, $this->classCorrespondence)) {
            $this->classesQty[$this->classCorrespondence[$class]]++;
        } else {
            $replacementClass = $this->getReplacementClass($dictionaryShip, $class);
            $this->classCorrespondence[$class] = $replacementClass;
            if (array_key_exists($replacementClass, $this->classesQty)) {
                $this->classesQty[$replacementClass]++;
            } else {
                $this->classesQty[$replacementClass] = 1;
            }
        }

        $newClassName = $this->truncate($this->classCorrespondence[$class]);
        $newClassName = $newClassName . '#' . $this->classesQty[$this->classCorrespondence[$class]];

        return new Ship(
            $currentName,
            $dictionary->getShipsList()[$currentName]->getFsName(),
            $newClassName
        );
    }

    private function getReplacementClass(DictionaryShip $dictionaryShip, string $class): string
    {
        try {
            $newClass = $dictionaryShip->getRandomSimilarShip();
        } catch (NoShipException $exception) {
            $newClass = $class;
        }

        return $newClass;
    }
}
