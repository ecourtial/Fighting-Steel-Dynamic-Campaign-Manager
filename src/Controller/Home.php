<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       19/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Controller;

use App\Core\Tas\Scenario\ScenarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{
    private ScenarioRepository $scenarioRepository;

    public function __construct(ScenarioRepository $scenarioRepository)
    {
        $this->scenarioRepository = $scenarioRepository;
    }

    /** @Route("/", name="home") */
    public function __invoke(): Response
    {
        try {
            $scenarios = $this->scenarioRepository->getAll();
        } catch (\Throwable $exception) {
            return new JsonResponse(['messages' => [$exception->getMessage()]], 500);
        }

        return $this->render(
            'main.html.twig',
            ['scenarios' => $scenarios]
        )
            ->setSharedMaxAge(0)
            ->setMaxAge(0);
    }
}
