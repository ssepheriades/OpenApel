<?php

declare(strict_types=1);

namespace App\Service;

use App\AppTimezone;
use App\Entity\Event;
use App\Enum\EventState;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventIcsFactory
{
    private const string PRODID = '-//OpenApel//CMS//FR';

    public function __construct(
        private readonly IcsEncoder $encoder,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%app.instance%')]
        private readonly string $appInstance,
    ) {
    }

    /**
     * @param list<Event> $events
     */
    public function renderFeed(string $siteName, array $events, \DateTimeImmutable $now): string
    {
        $calendarName = $siteName.' — Agenda';
        $lines = $this->calendarHeader($calendarName, includePublishMethod: true);
        $lines[] = $this->encoder->property(
            'X-WR-CALDESC',
            $this->encoder->text('Événements de l\'association (hors vacances et jours fériés).'),
        );

        foreach ($events as $event) {
            foreach ($this->veventLines($event, $now) as $line) {
                $lines[] = $line;
            }
        }

        $lines[] = 'END:VCALENDAR';

        return $this->encoder->document($lines);
    }

    public function renderEvent(Event $event, \DateTimeImmutable $now): string
    {
        $title = $event->getTitle() ?? 'Événement';
        $lines = $this->calendarHeader($title, includePublishMethod: false);
        foreach ($this->veventLines($event, $now) as $line) {
            $lines[] = $line;
        }
        $lines[] = 'END:VCALENDAR';

        return $this->encoder->document($lines);
    }

    /**
     * @return list<string>
     */
    private function calendarHeader(string $calendarName, bool $includePublishMethod): array
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            $this->encoder->property('PRODID', self::PRODID),
            'CALSCALE:GREGORIAN',
        ];

        if ($includePublishMethod) {
            $lines[] = 'METHOD:PUBLISH';
        }

        $escapedName = $this->encoder->text($calendarName);
        $lines[] = $this->encoder->property('NAME', $escapedName);
        $lines[] = $this->encoder->property('X-WR-CALNAME', $escapedName);
        $lines[] = $this->encoder->property('X-WR-TIMEZONE', AppTimezone::NAME);
        $lines[] = $this->encoder->property('REFRESH-INTERVAL', 'PT6H', ['VALUE' => 'DURATION']);
        $lines[] = $this->encoder->property('X-PUBLISHED-TTL', 'PT6H');

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function veventLines(Event $event, \DateTimeImmutable $now): array
    {
        $id = $event->getId();
        $title = $event->getTitle();
        $startsAt = $event->getStartsAt();
        $slug = $event->getSlug();
        if (null === $id || null === $title || null === $startsAt || null === $slug) {
            throw new \LogicException('Event is not ready for iCalendar export.');
        }

        $utc = new \DateTimeZone('UTC');
        $stamp = $now->setTimezone($utc)->format('Ymd\THis\Z');
        $updatedAt = $event->getUpdatedAt()?->setTimezone($utc)->format('Ymd\THis\Z') ?? $stamp;
        $cancelled = EventState::Cancelled === $event->getState();
        $summary = $cancelled ? 'Annulé : '.$title : $title;

        $lines = [
            'BEGIN:VEVENT',
            $this->encoder->property('UID', sprintf('event-%d@%s.openapel', $id, $this->appInstance)),
            $this->encoder->property('DTSTAMP', $stamp),
            $this->encoder->property('LAST-MODIFIED', $updatedAt),
            $this->encoder->property('SUMMARY', $this->encoder->text($summary)),
            $this->encoder->property('STATUS', $cancelled ? 'CANCELLED' : 'CONFIRMED'),
        ];

        foreach ($this->scheduleProperties($event, $startsAt) as $line) {
            $lines[] = $line;
        }

        $location = $event->getLocation();
        if (null !== $location && '' !== $location) {
            $lines[] = $this->encoder->property('LOCATION', $this->encoder->text($location));
        }

        $description = $this->description($event);
        if ('' !== $description) {
            $lines[] = $this->encoder->property('DESCRIPTION', $this->encoder->text($description));
        }

        $type = $event->getType();
        if (null !== $type) {
            $lines[] = $this->encoder->property('CATEGORIES', $this->encoder->text($type->label()));
        }

        $lines[] = $this->encoder->property('URL', $this->eventUrl($slug));
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function scheduleProperties(Event $event, \DateTimeImmutable $startsAt): array
    {
        if ($event->isAllDay()) {
            $tz = new \DateTimeZone(AppTimezone::NAME);
            $startDay = $startsAt->setTimezone($tz);
            $inclusiveEnd = ($event->getEndsAt() ?? $startDay)->setTimezone($tz);
            $exclusiveEnd = $inclusiveEnd->modify('+1 day');

            return [
                $this->encoder->property('DTSTART', $startDay->format('Ymd'), ['VALUE' => 'DATE']),
                $this->encoder->property('DTEND', $exclusiveEnd->format('Ymd'), ['VALUE' => 'DATE']),
            ];
        }

        $end = $event->getEndsAt() ?? $startsAt->modify('+1 hour');

        return [
            $this->encoder->property('DTSTART', $this->utcTimestamp($startsAt)),
            $this->encoder->property('DTEND', $this->utcTimestamp($end)),
        ];
    }

    private function utcTimestamp(\DateTimeImmutable $date): string
    {
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
    }

    private function description(Event $event): string
    {
        $source = $event->getShortDescription() ?: ($event->getDescription() ?? '');
        $plain = $this->markdownToPlain($source);
        $parts = [];
        if ('' !== $plain) {
            $parts[] = $plain;
        }

        $location = $event->getLocation();
        if (null !== $location && '' !== $location) {
            $parts[] = 'Lieu : '.$location;
        }

        $ticketing = $event->getTicketingUrl();
        if (null !== $ticketing && '' !== $ticketing) {
            $parts[] = 'Billetterie : '.$ticketing;
        }

        return implode("\n\n", $parts);
    }

    private function markdownToPlain(string $markdown): string
    {
        $text = preg_replace('/```[\s\S]*?```/', '', $markdown) ?? $markdown;
        $text = preg_replace('/`([^`]+)`/', '$1', $text) ?? $text;
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1 ($2)', $text) ?? $text;
        $text = preg_replace('/^#{1,6}\s+/m', '', $text) ?? $text;
        $text = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $text) ?? $text;
        $text = preg_replace('/(\*|_)(.*?)\1/', '$2', $text) ?? $text;

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function eventUrl(string $slug): string
    {
        return $this->urlGenerator->generate(
            'spa_index',
            ['reactRouting' => 'agenda/'.$slug],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
