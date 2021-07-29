<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Fs;

use App\ScenarioGenerator\ScenarioGenerator as ScenarioGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class ScenarioGenerator extends AbstractController
{
    private RequestStack $requestStack;
    private LoggerInterface $logger;
    private ScenarioGeneratorService $scenarioGeneratorService;

    public function __construct(
        RequestStack $requestStack,
        LoggerInterface $logger,
        ScenarioGeneratorService $scenarioGeneratorService
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->scenarioGeneratorService = $scenarioGeneratorService;
    }

    /** @Route("/fs/scenario-generator", name="scenarioGenerator", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $status = 200;

        try {
            $scenarioName = $this->scenarioGeneratorService->generate(
                $this->requestStack->getCurrentRequest()->get('code', null),
                (int) $this->requestStack->getCurrentRequest()->get('period', null),
                (bool) $this->requestStack->getCurrentRequest()->get('mixedNavies', null),
            );
            $message = "The scenario with the following name has been generated : '$scenarioName'";
        } catch (\Throwable $exception) {
            $status = 500;
            $message = 'An error occurred: ' . $exception->getMessage();
            $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
        }

        return new JsonResponse(['messages' => [$message]], $status);
    }
}
