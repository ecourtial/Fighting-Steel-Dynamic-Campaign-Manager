<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Tas;

use App\NameSwitcher\ScenarioManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class TasToFs extends AbstractController
{
    private RequestStack $requestStack;
    private ScenarioManager $scenarioManager;
    private LoggerInterface $logger;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $logger,
        ScenarioManager $scenarioManager
    ) {
        $this->requestStack = $requestStack;
        $this->scenarioManager = $scenarioManager;
        $this->logger = $logger;
    }

    /** @Route("/tas/tas-to-fs", name="tasToFs", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $scenarioKey = $this->requestStack->getCurrentRequest()->get('scenario', null);
        $oneShip = $this->requestStack->getCurrentRequest()->get('oneShip', null);
        $switchLevel = $this->requestStack->getCurrentRequest()->get('switchLevel', null);
        $status = 200;

        if (is_string($scenarioKey) && is_string($oneShip) && is_string($switchLevel)) {
            try {
                $this->scenarioManager->fromTasToFs($scenarioKey, $oneShip, $switchLevel);
                $message = 'Translation from TAS to FS completed.';
            } catch (\Throwable $exception) {
                $status = 500;
                $message = 'An error occurred: ' . $exception->getMessage();
                $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
            }
        } else {
            $status = 400;
            $message = 'Invalid request data!';
        }

        return new JsonResponse(['messages' => [$message]], $status);
    }
}
