<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Tas;

use App\NameSwitcher\ScenarioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TasToFs extends AbstractController
{
    private RequestStack $requestStack;
    private ScenarioManager $scenarioManager;

    public function __construct(
        RequestStack $requestStack,
        ScenarioManager $scenarioManager
    ) {
        $this->requestStack = $requestStack;
        $this->scenarioManager = $scenarioManager;
    }

    /** @Route("/tas/tas-to", name="tasToFs", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $scenarioKey = $this->requestStack->getCurrentRequest()->get('scenario', null);
        $oneShip = $this->requestStack->getCurrentRequest()->get('oneShip', null);
        $switchLevel = $this->requestStack->getCurrentRequest()->get('switchLevel', null);

        if (is_string($scenarioKey) && is_string($oneShip) && is_string($switchLevel)) {
            try {
                $this->scenarioManager->fromTasToFs($scenarioKey, $oneShip, $switchLevel);
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
