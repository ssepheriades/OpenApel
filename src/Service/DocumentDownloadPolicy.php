<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DocumentRepository;

/**
 * Whether GET /media/documents/{filename} may serve the file.
 *
 * Visible and hidden files are downloadable by anyone who has the URL.
 * Archived, unknown or orphan files 404 for the public; ROLE_ADMIN can still open them.
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

        if ($document->getVisibility()->isPubliclyDownloadable()) {
            return true;
        }

        return $isAdmin;
    }
}
