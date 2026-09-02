<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\ApiResource\ContactMessageResource;
use App\Entity\ContactMessage;
use App\Entity\Grade;
use App\Entity\SchoolClass;
use App\Entity\SiteSettings;
use App\Repository\SchoolClassRepository;
use App\Repository\SiteSettingsRepository;
use App\Service\ContactMessageService;
use App\Service\SiteSettingsProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

final class ContactMessageServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private SchoolClassRepository&MockObject $schoolClassRepository;
    private SiteSettingsRepository&MockObject $settingsRepository;
    private MailerInterface&MockObject $mailer;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->schoolClassRepository = $this->createMock(SchoolClassRepository::class);
        $this->settingsRepository = $this->createMock(SiteSettingsRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSubmitPersistsAndSendsEmail(): void
    {
        $schoolClass = (new SchoolClass())
            ->setName('CE1-A')
            ->setGrade((new Grade())->setName('CE1')->setWeight(10));

        $this->schoolClassRepository->expects(self::once())->method('find')->with(7)->willReturn($schoolClass);
        $this->settingsRepository->method('getOrCreate')->willReturn(
            (new SiteSettings())->setContactEmail('contact@example.org'),
        );

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(static function (ContactMessage $message): bool {
                self::assertSame('Marie Dupont', $message->getName());
                self::assertSame('marie@example.org', $message->getEmail());
                self::assertSame('0601020304', $message->getPhone());
                self::assertSame('Question cantine', $message->getSubject());
                self::assertSame('Comment ça se passe ?', $message->getMessage());
                self::assertSame('CE1-A', $message->getSchoolClass()?->getName());
                self::assertFalse($message->isProcessed());
                self::assertFalse($message->isArchived());

                return true;
            }));
        $this->entityManager->expects(self::once())->method('flush');

        $this->mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (TemplatedEmail $email): bool {
                self::assertSame('contact@example.org', $email->getTo()[0]->getAddress());
                self::assertSame('marie@example.org', $email->getReplyTo()[0]->getAddress());
                self::assertSame('[Contact] Question cantine', $email->getSubject());

                return true;
            }));
        $this->logger->expects(self::never())->method('error');

        $this->service()->submit($this->input(schoolClassId: 7, phone: '0601020304'));
    }

    public function testSubmitSkipsEmailWhenContactEmailIsMissing(): void
    {
        $this->settingsRepository->method('getOrCreate')->willReturn(new SiteSettings());
        $this->entityManager->expects(self::once())->method('persist');
        $this->entityManager->expects(self::once())->method('flush');
        $this->mailer->expects(self::never())->method('send');

        $this->service()->submit($this->input());
    }

    public function testSubmitSkipsEmailWhenSendingIsDisabled(): void
    {
        $this->settingsRepository->method('getOrCreate')->willReturn(
            (new SiteSettings())
                ->setContactEmail('contact@example.org')
                ->setContactEmailEnabled(false),
        );
        $this->entityManager->expects(self::once())->method('persist');
        $this->entityManager->expects(self::once())->method('flush');
        $this->mailer->expects(self::never())->method('send');

        $this->service()->submit($this->input());
    }

    public function testSubmitIgnoresHoneypotWithoutPersisting(): void
    {
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');
        $this->mailer->expects(self::never())->method('send');

        $this->service()->submit($this->input(website: 'https://spam.example'));
    }

    public function testSubmitLogsMailFailureAfterPersist(): void
    {
        $this->settingsRepository->method('getOrCreate')->willReturn(
            (new SiteSettings())->setContactEmail('contact@example.org'),
        );
        $this->entityManager->expects(self::once())->method('persist');
        $this->entityManager->expects(self::once())->method('flush');
        $this->mailer->expects(self::once())
            ->method('send')
            ->willThrowException(new TransportException('smtp down'));
        $this->logger->expects(self::once())->method('error');

        $this->service()->submit($this->input());
    }

    private function service(): ContactMessageService
    {
        return new ContactMessageService(
            $this->entityManager,
            $this->schoolClassRepository,
            new SiteSettingsProvider($this->settingsRepository, new ArrayAdapter()),
            $this->mailer,
            $this->logger,
        );
    }

    private function input(
        ?int $schoolClassId = null,
        ?string $phone = null,
        ?string $website = null,
    ): ContactMessageResource {
        return new ContactMessageResource(
            name: 'Marie Dupont',
            email: 'marie@example.org',
            phone: $phone,
            subject: 'Question cantine',
            message: 'Comment ça se passe ?',
            schoolClassId: $schoolClassId,
            website: $website,
        );
    }
}
