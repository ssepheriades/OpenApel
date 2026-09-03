<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Locked catalogue of public copy slots. Staff edit the row, they do not create slugs.
 */
enum PageSlug: string
{
    case Home = 'home';
    case News = 'news';
    case Agenda = 'agenda';
    case Faq = 'faq';
    case Team = 'team';
    case Contact = 'contact';
    case MentionsLegales = 'mentions-legales';
    case PolitiqueDeConfidentialite = 'politique-de-confidentialite';

    public function kind(): PageKind
    {
        return match ($this) {
            self::MentionsLegales, self::PolitiqueDeConfidentialite => PageKind::Document,
            default => PageKind::Section,
        };
    }

    /**
     * Whether the public view renders Markdown body for this slot.
     */
    public function usesBody(): bool
    {
        return match ($this) {
            self::Home, self::MentionsLegales, self::PolitiqueDeConfidentialite => true,
            default => false,
        };
    }

    /**
     * Whether the public view renders a hero subtitle (chapô) for this slot.
     */
    public function usesSubtitle(): bool
    {
        return match ($this) {
            self::News, self::Agenda, self::Faq, self::Team, self::Contact => true,
            default => false,
        };
    }

    /**
     * Whether staff can hide this slot from the public site (footer + route).
     */
    public function usesVisibility(): bool
    {
        return match ($this) {
            self::Home, self::Contact => false,
            default => true,
        };
    }

    public function defaultTitle(): string
    {
        return match ($this) {
            self::Home => 'Accueil',
            self::News => 'Actualités',
            self::Agenda => 'Agenda',
            self::Faq => 'FAQ',
            self::Team => 'Équipe',
            self::Contact => 'Contact',
            self::MentionsLegales => 'Mentions légales',
            self::PolitiqueDeConfidentialite => 'Politique de confidentialité',
        };
    }

    public function defaultSubtitle(): ?string
    {
        return match ($this) {
            self::News => 'Les nouvelles de l\'association',
            self::Agenda => 'Découvrez tous les événements à venir et l\'historique de nos activités',
            self::Faq => 'Les questions les plus fréquentes des familles',
            self::Team => null,
            self::Contact => 'Une question ? Écrivez-nous, nous vous répondrons dès que possible.',
            default => null,
        };
    }

    public function defaultBody(): ?string
    {
        return match ($this) {
            self::MentionsLegales => "À compléter par l'association.\n\nIndiquez l'éditeur du site, l'hébergeur et les mentions obligatoires.",
            self::PolitiqueDeConfidentialite => "À compléter par l'association.\n\nDécrivez les données collectées, leur usage et les droits des familles.",
            default => null,
        };
    }

    /**
     * Public path for document pages. Sections keep their existing Vue routes.
     */
    public function publicPath(): ?string
    {
        return $this->kind() === PageKind::Document ? '/'.$this->value : null;
    }
}
