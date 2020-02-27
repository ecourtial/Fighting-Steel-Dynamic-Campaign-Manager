<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       27/02/2020 (dd-mm-YYYY)
 */

namespace App\Controller\Test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Test extends AbstractController
{
    /** @Route("/test", name="test") */
    public function __invoke(): Response
    {
        echo 'THIS IS A TEST ROUTE FOR TESTING SOME COMPONENTS';
        exit;
    }
}
