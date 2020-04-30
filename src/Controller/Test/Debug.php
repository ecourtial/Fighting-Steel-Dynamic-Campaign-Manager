<?php

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

declare(strict_types=1);

namespace App\Controller\Test;

use App\Core\Exception\SecurityException;
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
            throw new SecurityException('Please make sur that no code is executed!');
        }

        return new JsonResponse(['msg' => 'This route is for debug only']);
    }
}
