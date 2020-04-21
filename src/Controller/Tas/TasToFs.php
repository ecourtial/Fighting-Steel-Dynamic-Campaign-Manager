<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Controller\Tas;

use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\ScenarioProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TasToFs extends AbstractController
{
    private RequestStack $requestStack;
    private ScenarioProcessor $scenarioProcessor;
    private DictionaryFactory $dictionaryFactory;
    private ScenarioRepository $scenarioRepository;

    public function __construct(
        RequestStack $requestStack,
        ScenarioProcessor $scenarioProcessor,
        DictionaryFactory $dictionaryFactory,
        ScenarioRepository $scenarioRepository
    ) {
        $this->requestStack = $requestStack;
        $this->scenarioProcessor = $scenarioProcessor;
        $this->dictionaryFactory = $dictionaryFactory;
        $this->scenarioRepository = $scenarioRepository;
    }

    /** @Route("/tas/tas-to", name="tasToFs", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $scenarioKey = $this->requestStack->getCurrentRequest()->get('scenario', null);
        $oneShip = $this->requestStack->getCurrentRequest()->get('oneShip', null);
        $switchLevel = $this->requestStack->getCurrentRequest()->get('switchLevel', null);

        if (is_string($scenarioKey) && is_string($oneShip) && is_string($switchLevel)) {
            try {
                $scenario = $this->scenarioRepository->getOneWillAllData($scenarioKey);
                $this->scenarioProcessor->convertFromTasToFs(
                    $oneShip,
                    $this->dictionaryFactory->getDictionary($scenario->getDictionaryPath()),
                    $scenario->getFsShips()
                );
                $errors = [];
            } catch (\Throwable $exception) {
                $errors = [$exception->getMessage()];
            }
        } else {
            $errors = ['Invalid request data!'];
        }

        return new JsonResponse($errors);
    }
}
