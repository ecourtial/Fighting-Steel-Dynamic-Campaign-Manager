<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       05/04/2020 (dd-mm-YYYY)
 */

namespace App\NameSwitcher\Switcher;

use App\Core\Fs\FsShipInterface;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\Ship as DictionaryShip;
use App\NameSwitcher\Exception\NoShipException;
use App\NameSwitcher\Transformer\Ship;

class ErrorSwitcher extends ClassSwitcher
{
    public const MIN_THRESHOLD_ERROR_PROBABILITY = 0;
    public const MAX_THRESHOLD_ERROR_PROBABILITY = 3;
    public const NO_ERROR_PROBABILITY_VALUE = 1;

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
     */
    protected function addNewCorrespondence(FsShipInterface $fsShip, Dictionary $dictionary): Ship
    {
        $class = $fsShip->getClass();
        $currentName = $fsShip->getName();
        $dictionaryShip = $dictionary->findOneShip(['TasName' => $currentName]);

        $isError = (random_int(static::MIN_THRESHOLD_ERROR_PROBABILITY, static::MAX_THRESHOLD_ERROR_PROBABILITY))
            === static::NO_ERROR_PROBABILITY_VALUE ? false : true;

        if ($isError) {
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
        } else {
            // In the 1/4 case the crew got right: just switch class, ONLY for this ship
            return parent::addNewCorrespondence($fsShip, $dictionary);
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
