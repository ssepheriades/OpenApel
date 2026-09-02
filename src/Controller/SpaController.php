<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpaController extends AbstractController
{
    #[Route(
        '/{reactRouting}',
        name: 'spa_index',
        requirements: ['reactRouting' => '^(?!admin|api|build|bundles|uploads|media).+'],
        defaults: ['reactRouting' => null],
        priority: -1,
    )]
    public function index(): Response
    {
        return $this->render('spa.html.twig');
    }
}
