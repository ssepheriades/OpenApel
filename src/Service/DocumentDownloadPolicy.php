<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\DocumentVisibility;
use App\Repository\DocumentRepository;

/**
 * Whether GET /media/documents/{filename} may serve the file.
 * Hidden or unknown files 404 for the public; staff with ROLE_ADMIN can still open them.
 */
final readonly class DocumentDownloadPolicy
{
    public function __construct(
        private DocumentRepository $documentRepository,
    ) {
    }

    public function allowsDownload(string $filename, bool $isAdmin): bool
    {
        $document = $this->documentRepository->findOneByFilename($filename);
        if (null === $document) {
            return false;
        }

        if (DocumentVisibility::Visible === $document->getVisibility()) {
            return true;
        }

        return $isAdmin;
    }
}
