<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Заглушка для главной страницы.
 */
final class DefaultController extends AbstractController
{
    #[Route('/', methods: Request::METHOD_GET, name: 'app_home')]
    public function index(): Response
    {
        throw $this->createNotFoundException('No home page');
    }

    #[Route('/ping', methods: Request::METHOD_GET, name: 'app_ping')]
    public function ping(): Response
    {
        return new Response('pong', headers: ['Content-Type' => 'text/plain']);
    }
}
