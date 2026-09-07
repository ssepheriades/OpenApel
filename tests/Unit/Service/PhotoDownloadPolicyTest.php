<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Event;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\EventVisibility;
use App\Enum\MediaDownloadAccess;
use App\Enum\PostState;
use App\Enum\UserRole;
use App\Repository\EventRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\PhotoDownloadPolicy;
use PHPUnit\Framework\TestCase;

final class PhotoDownloadPolicyTest extends TestCase
{
    public function testUnknownFilenameIsDenied(): void
    {
        self::assertSame(MediaDownloadAccess::Denied, $this->policy()->access('missing.webp'));
    }

    public function testPublishedPostCoverIsPublic(): void
    {
        $policy = $this->policy(posts: [$this->post(PostState::Published)], filename: 'cover.webp');

        self::assertSame(MediaDownloadAccess::Public, $policy->access('cover.webp'));
    }

    public function testDraftPostCoverIsStaffOnly(): void
    {
        $policy = $this->policy(posts: [$this->post(PostState::Draft)], filename: 'draft.webp');

        self::assertSame(MediaDownloadAccess::Staff, $policy->access('draft.webp'));
    }

    public function testHiddenEventImageIsStaffOnly(): void
    {
        $policy = $this->policy(events: [$this->event(EventVisibility::Hidden)], filename: 'hero.webp');

        self::assertSame(MediaDownloadAccess::Staff, $policy->access('hero.webp'));
    }

    public function testGreyedOutEventImageIsPublic(): void
    {
        $policy = $this->policy(events: [$this->event(EventVisibility::GreyedOut)], filename: 'flyer.webp');

        self::assertSame(MediaDownloadAccess::Public, $policy->access('flyer.webp'));
    }

    public function testVisibleEventImageIsPublic(): void
    {
        $policy = $this->policy(events: [$this->event(EventVisibility::Visible)], filename: 'hero.webp');

        self::assertSame(MediaDownloadAccess::Public, $policy->access('hero.webp'));
    }

    public function testActiveMemberPhotoIsPublic(): void
    {
        $policy = $this->policy(users: [$this->user(active: true, member: true)], filename: 'member.webp');

        self::assertSame(MediaDownloadAccess::Public, $policy->access('member.webp'));
    }

    public function testInactiveMemberPhotoIsStaffOnly(): void
    {
        $policy = $this->policy(users: [$this->user(active: false, member: true)], filename: 'member.webp');

        self::assertSame(MediaDownloadAccess::Staff, $policy->access('member.webp'));
    }

    public function testActiveAdminWithoutMemberRoleIsStaffOnly(): void
    {
        $policy = $this->policy(users: [$this->user(active: true, member: false)], filename: 'staff.webp');

        self::assertSame(MediaDownloadAccess::Staff, $policy->access('staff.webp'));
    }

    public function testPublicOwnerWinsWhenAnotherOwnerIsStaffOnly(): void
    {
        $policy = $this->policy(
            posts: [$this->post(PostState::Draft)],
            events: [$this->event(EventVisibility::Visible)],
            filename: 'shared.webp',
        );

        self::assertSame(MediaDownloadAccess::Public, $policy->access('shared.webp'));
    }

    /**
     * @param Post[]  $posts
     * @param Event[] $events
     * @param User[]  $users
     */
    private function policy(array $posts = [], array $events = [], array $users = [], string $filename = 'missing.webp'): PhotoDownloadPolicy
    {
        $postRepository = $this->createMock(PostRepository::class);
        $eventRepository = $this->createMock(EventRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $postRepository->method('findByCoverImageFilename')->with($filename)->willReturn($posts);
        $eventRepository->method('findByImageFilename')->with($filename)->willReturn($events);
        $userRepository->method('findByPhotoFilename')->with($filename)->willReturn($users);

        return new PhotoDownloadPolicy($postRepository, $eventRepository, $userRepository);
    }

    private function post(PostState $state): Post
    {
        return (new Post())->setState($state);
    }

    private function event(EventVisibility $visibility): Event
    {
        return (new Event())->setVisibility($visibility);
    }

    private function user(bool $active, bool $member): User
    {
        $roles = $member ? [UserRole::Member->value] : [UserRole::Admin->value];

        return (new User())
            ->setRoles($roles)
            ->setIsActive($active);
    }
}
