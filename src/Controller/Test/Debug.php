<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       27/02/2020 (dd-mm-YYYY)
 */

namespace App\Controller\Test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Debug extends AbstractController
{
    private string $env;

    public function __construct(string $env)
    {
        $this->env = $env;
    }

    /** @Route("/test", name="test") */
    public function __invoke(): Response
    {
        if ('dev' === $this->env) {
            // Do debug stuff here.
            throw new \RuntimeException('Please make sur that no code is executed!');
        }

        return new JsonResponse(['msg' => 'This route is for debug only']);
    }
}
