<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RobotsTxtController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('/robots.txt', name: 'robots_txt', methods: ['GET'])]
    public function __invoke(): Response
    {
        $path = $this->projectDir.'/public/robots.txt';
        $content = is_file($path) ? file_get_contents($path) : false;
        if (false === $content) {
            throw $this->createNotFoundException();
        }

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
