<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\ContentTheme;
use App\Entity\Document;
use App\Enum\DocumentVisibility;
use App\Repository\DocumentRepository;
use App\Service\DocumentDownloadPolicy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DocumentDownloadPolicyTest extends TestCase
{
    private DocumentRepository&MockObject $documentRepository;
    private DocumentDownloadPolicy $policy;

    protected function setUp(): void
    {
        $this->documentRepository = $this->createMock(DocumentRepository::class);
        $this->policy = new DocumentDownloadPolicy($this->documentRepository);
    }

    public function testUnknownFilenameIsDenied(): void
    {
        $this->documentRepository->method('findOneByFilename')->with('missing.pdf')->willReturn(null);

        self::assertFalse($this->policy->allowsDownload('missing.pdf', false));
        self::assertFalse($this->policy->allowsDownload('missing.pdf', true));
    }

    public function testVisibleDocumentIsAllowedForAnyone(): void
    {
        $this->documentRepository->method('findOneByFilename')->with('public.pdf')->willReturn(
            $this->document(DocumentVisibility::Visible),
        );

        self::assertTrue($this->policy->allowsDownload('public.pdf', false));
        self::assertTrue($this->policy->allowsDownload('public.pdf', true));
    }

    public function testHiddenDocumentIsDeniedForAnonymous(): void
    {
        $this->documentRepository->method('findOneByFilename')->with('secret.pdf')->willReturn(
            $this->document(DocumentVisibility::Hidden),
        );

        self::assertFalse($this->policy->allowsDownload('secret.pdf', false));
    }

    public function testHiddenDocumentIsAllowedForAdmin(): void
    {
        $this->documentRepository->method('findOneByFilename')->with('secret.pdf')->willReturn(
            $this->document(DocumentVisibility::Hidden),
        );

        self::assertTrue($this->policy->allowsDownload('secret.pdf', true));
    }

    private function document(DocumentVisibility $visibility): Document
    {
        $theme = (new ContentTheme())
            ->setName('Association')
            ->setIcon('mdi-account-group')
            ->setWeight(20);

        return (new Document())
            ->setName('Compte rendu')
            ->setTheme($theme)
            ->setFilename('file.pdf')
            ->setVisibility($visibility);
    }
}
