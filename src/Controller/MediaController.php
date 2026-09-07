<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\MediaMapping;
use App\Service\MediaStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaStorage $mediaStorage,
    ) {
    }

    #[Route(
        '/media/{mapping}/{filename}',
        name: 'media_serve',
        requirements: [
            'mapping' => MediaMapping::ROUTE_REQUIREMENT,
            'filename' => MediaMapping::FILENAME_REQUIREMENT,
        ],
        methods: ['GET'],
    )]
    public function serve(MediaMapping $mapping, string $filename): Response
    {
        if (!$mapping->isPublic()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $file = $this->mediaStorage->path($mapping, $filename);
        if (null === $file) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file->getFilename());
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        if ($mapping->isPublic()) {
            $response->setPublic();
            $response->setMaxAge(86400);
        } else {
            $response->setPrivate();
        }

        return $response;
    }
}
