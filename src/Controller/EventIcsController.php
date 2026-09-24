<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Service\EventIcsFactory;
use App\Service\SchoolYear;
use App\Service\SiteSettingsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventIcsController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventIcsFactory $eventIcsFactory,
        private readonly SiteSettingsProvider $siteSettingsProvider,
        private readonly SchoolYear $schoolYear,
    ) {
    }

    #[Route('/calendar.ics', name: 'calendar_ics_feed', methods: ['GET'])]
    public function feed(Request $request): Response
    {
        $settings = $this->siteSettingsProvider->get();
        $now = new \DateTimeImmutable('now');
        $startsAtFrom = $this->schoolYear->currentStart(
            $now,
            new \DateTimeImmutable($settings->schoolYearStart),
            new \DateTimeImmutable($settings->schoolYearEnd),
        );
        $ics = $this->eventIcsFactory->renderFeed(
            $settings->siteName,
            $this->eventRepository->findForIcsFeed($startsAtFrom),
            $now,
        );

        return $this->icsResponse($request, $ics, 'agenda.ics');
    }

    #[Route(
        '/calendar/events/{slug}.ics',
        name: 'calendar_ics_event',
        requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'],
        methods: ['GET'],
    )]
    public function event(Request $request, string $slug): Response
    {
        $event = $this->eventRepository->findVisibleExportableBySlug($slug);
        if (null === $event) {
            throw $this->createNotFoundException();
        }

        $ics = $this->eventIcsFactory->renderEvent($event, new \DateTimeImmutable('now'));

        return $this->icsResponse($request, $ics, $slug.'.ics');
    }

    private function icsResponse(Request $request, string $ics, string $filename): Response
    {
        $response = new Response($ics, Response::HTTP_OK, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename),
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPublic();
        $response->setMaxAge(300);
        $response->setEtag(hash('sha256', $ics));
        $response->isNotModified($request);

        return $response;
    }
}
