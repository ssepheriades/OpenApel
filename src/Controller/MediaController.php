<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\MediaDownloadAccess;
use App\Enum\MediaMapping;
use App\Service\BrandingDownloadPolicy;
use App\Service\DocumentDownloadPolicy;
use App\Service\MediaStorage;
use App\Service\PhotoDownloadPolicy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaStorage $mediaStorage,
        private readonly DocumentDownloadPolicy $documentDownloadPolicy,
        private readonly PhotoDownloadPolicy $photoDownloadPolicy,
        private readonly BrandingDownloadPolicy $brandingDownloadPolicy,
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

        if (MediaMapping::Documents === $mapping && !$this->documentDownloadPolicy->allowsDownload(
            $filename,
            $this->isGranted('ROLE_ADMIN'),
        )) {
            throw $this->createNotFoundException();
        }

        $file = $this->mediaStorage->path($mapping, $filename);
        if (null === $file) {
            throw $this->createNotFoundException();
        }

        $access = $this->resolveAccess($mapping, $filename);
        if (MediaDownloadAccess::Denied === $access) {
            throw $this->createNotFoundException();
        }
        if (MediaDownloadAccess::Staff === $access && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($file);
        $disposition = str_ends_with(strtolower($filename), '.svg')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;
        $response->setContentDisposition($disposition, $file->getFilename());
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Security-Policy', "default-src 'none'");

        if (MediaMapping::Documents === $mapping || MediaDownloadAccess::Staff === $access) {
            $response->setPrivate();
        } elseif (MediaDownloadAccess::Public === $access || $mapping->isPublic()) {
            $response->setPublic();
            $response->setMaxAge(86400);
        } else {
            $response->setPrivate();
        }

        return $response;
    }

    private function resolveAccess(MediaMapping $mapping, string $filename): MediaDownloadAccess
    {
        return match ($mapping) {
            MediaMapping::Photos => $this->photoDownloadPolicy->access($filename),
            MediaMapping::Branding => $this->brandingDownloadPolicy->access($filename),
            MediaMapping::Documents => MediaDownloadAccess::Public,
        };
    }
}
