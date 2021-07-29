<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Fs;

use App\NameSwitcher\ScenarioManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FsToTas extends AbstractController
{
    private ScenarioManager $scenarioManager;
    private LoggerInterface $logger;

    public function __construct(ScenarioManager $scenarioManager, LoggerInterface $logger)
    {
        $this->scenarioManager = $scenarioManager;
        $this->logger = $logger;
    }

    /** @Route("/fs/fs-to-tas", name="fsToTas", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        $status = 200;

        try {
            $this->scenarioManager->fromFsToTas();
            $message = 'Translation from FS to TAS completed.';
        } catch (\Throwable $exception) {
            $status = 500;
            $message = 'An error occurred: ' . $exception->getMessage();
            $this->logger->error($exception->getMessage() . ': ' . $exception->getTraceAsString());
        }

        return new JsonResponse(['messages' => [$message]], $status);
    }
}
