<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller\Fs;

use App\NameSwitcher\ScenarioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FsToTas extends AbstractController
{
    private ScenarioManager $scenarioManager;

    public function __construct(ScenarioManager $scenarioManager)
    {
        $this->scenarioManager = $scenarioManager;
    }

    /** @Route("/fs/fs-to-tas", name="fsToTas", methods={"POST"}) */
    public function __invoke(): JsonResponse
    {
        try {
            $this->scenarioManager->fromFsToTas();
            $errors = [];
        } catch (\Throwable $exception) {
            $errors = [$exception->getMessage()];
        }

        return new JsonResponse($errors);
    }
}
