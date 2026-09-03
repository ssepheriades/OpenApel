<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\PageKind;
use App\Enum\PageSlug;
use PHPUnit\Framework\TestCase;

final class PageSlugTest extends TestCase
{
    public function testCatalogueCoversPublicSlots(): void
    {
        $slugs = array_map(static fn (PageSlug $slug): string => $slug->value, PageSlug::cases());

        self::assertSame(
            [
                'home',
                'news',
                'agenda',
                'faq',
                'team',
                'contact',
                'mentions-legales',
                'politique-de-confidentialite',
            ],
            $slugs,
        );
    }

    public function testListingSectionsExposeAChapoNotABody(): void
    {
        foreach ([PageSlug::News, PageSlug::Agenda, PageSlug::Faq, PageSlug::Team, PageSlug::Contact] as $slug) {
            self::assertSame(PageKind::Section, $slug->kind());
            self::assertTrue($slug->usesSubtitle());
            self::assertFalse($slug->usesBody());
        }
    }

    public function testHomeAndLegalDocumentsUseABody(): void
    {
        self::assertTrue(PageSlug::Home->usesBody());
        self::assertFalse(PageSlug::Home->usesSubtitle());
        self::assertTrue(PageSlug::MentionsLegales->usesBody());
        self::assertTrue(PageSlug::PolitiqueDeConfidentialite->usesBody());
        self::assertSame('/mentions-legales', PageSlug::MentionsLegales->publicPath());
        self::assertNull(PageSlug::Faq->publicPath());
        self::assertTrue(PageSlug::MentionsLegales->usesVisibility());
        self::assertTrue(PageSlug::PolitiqueDeConfidentialite->usesVisibility());
        self::assertTrue(PageSlug::Faq->usesVisibility());
        self::assertTrue(PageSlug::News->usesVisibility());
        self::assertTrue(PageSlug::Agenda->usesVisibility());
        self::assertTrue(PageSlug::Team->usesVisibility());
        self::assertFalse(PageSlug::Home->usesVisibility());
        self::assertFalse(PageSlug::Contact->usesVisibility());
    }

    public function testTeamDefaultTitleIsNeutral(): void
    {
        self::assertSame('Équipe', PageSlug::Team->defaultTitle());
    }
}
