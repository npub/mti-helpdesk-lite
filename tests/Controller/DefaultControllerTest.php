<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class DefaultControllerTest extends WebTestCase
{
    public function testPing(): void
    {
        $client = self::createClient();
        $client->request('GET', '/ping');

        self::assertResponseIsSuccessful();
    }
}
