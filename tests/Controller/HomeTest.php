<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Controller;

use App\Tests\Controller\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    use ResponseTrait;

    public function testHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        static::assertNotFalse(strpos($response->getContent(), 'Fighting Steel'));
        $this->checkResponse($response);
    }
}
