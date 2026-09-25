<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Event;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\EventVisibility;
use App\Enum\MediaDownloadAccess;
use App\Enum\PostState;
use App\Enum\UserRole;
use App\Repository\EventRepository;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

/**
 * Whether GET /media/photos/{filename} may serve the file.
 * Matches public API listing: published posts, visible/greyed events, active team members,
 * and visible catalogue page covers.
 * Unknown files 404 for everyone; unpublished owners are staff-only (no public cache).
 */
final readonly class PhotoDownloadPolicy
{
    public function __construct(
        private PostRepository $postRepository,
        private EventRepository $eventRepository,
        private UserRepository $userRepository,
        private PageRepository $pageRepository,
    ) {
    }

    public function access(string $filename): MediaDownloadAccess
    {
        $posts = $this->postRepository->findByCoverImageFilename($filename);
        $events = $this->eventRepository->findByImageFilename($filename);
        $users = $this->userRepository->findByPhotoFilename($filename);
        $pages = $this->pageRepository->findByCoverImageFilename($filename);

        if ([] === $posts && [] === $events && [] === $users && [] === $pages) {
            return MediaDownloadAccess::Denied;
        }

        foreach ($posts as $post) {
            if ($this->isPostPublic($post)) {
                return MediaDownloadAccess::Public;
            }
        }

        foreach ($events as $event) {
            if ($this->isEventPublic($event)) {
                return MediaDownloadAccess::Public;
            }
        }

        foreach ($users as $user) {
            if ($this->isUserPublic($user)) {
                return MediaDownloadAccess::Public;
            }
        }

        foreach ($pages as $page) {
            if ($this->isPagePublic($page)) {
                return MediaDownloadAccess::Public;
            }
        }

        return MediaDownloadAccess::Staff;
    }

    private function isPostPublic(Post $post): bool
    {
        return PostState::Published === $post->getState();
    }

    private function isEventPublic(Event $event): bool
    {
        return \in_array($event->getVisibility(), [EventVisibility::Visible, EventVisibility::GreyedOut], true);
    }

    private function isUserPublic(User $user): bool
    {
        return $user->isActive() && $user->hasRole(UserRole::Member);
    }

    private function isPagePublic(Page $page): bool
    {
        return $page->isVisible();
    }
}
