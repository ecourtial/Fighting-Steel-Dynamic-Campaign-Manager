<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Tas;

use App\NameSwitcher\Validator\ScenarioValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class ScenarioValidation extends AbstractController
{
    private RequestStack $requestStack;
    private ScenarioValidator $scenarioValidator;
    private LoggerInterface $logger;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $logger,
        ScenarioValidator $scenarioValidator
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->scenarioValidator = $scenarioValidator;
    }

    /** @Route("/tas/scenario-validation", name="validateScenario", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $scenarioKey = $this->requestStack->getCurrentRequest()->get('scenario', null);

        if (is_string($scenarioKey)) {
            try {
                $errors = $this->scenarioValidator->validate($scenarioKey);
            } catch (\Throwable $exception) {
                $errors = [$exception->getMessage()];
                $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
            }
        } else {
            $errors = ['Missing scenario key'];
        }

        return new JsonResponse($errors);
    }
}
