<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Validator\Exception\ValidationException;
use App\ApiResource\ContactMessageResource;
use App\Entity\ContactMessage;
use App\Entity\SchoolClass;
use App\Repository\SchoolClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Persists a public contact submission, then emails the staff when sending is enabled
 * and a recipient is configured. Mail failures are logged and swallowed: the message
 * is already in the database for the back-office.
 */
final readonly class ContactMessageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SchoolClassRepository $schoolClassRepository,
        private SiteSettingsProvider $settingsProvider,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function submit(ContactMessageResource $input): void
    {
        if (null !== $input->hp && '' !== trim($input->hp)) {
            return;
        }

        $contactMessage = (new ContactMessage())
            ->setName(trim($input->name))
            ->setEmail(trim($input->email))
            ->setPhone($this->normalizeOptional($input->phone))
            ->setSubject(trim($input->subject))
            ->setMessage(trim($input->message))
            ->setSchoolClass($this->resolveSchoolClass($input));

        $this->entityManager->persist($contactMessage);
        $this->entityManager->flush();

        $this->sendNotification($contactMessage);
    }

    private function resolveSchoolClass(ContactMessageResource $input): ?SchoolClass
    {
        if (null === $input->schoolClassId) {
            return null;
        }

        $schoolClass = $this->schoolClassRepository->find($input->schoolClassId);
        if (null === $schoolClass) {
            throw new ValidationException(new ConstraintViolationList([
                new ConstraintViolation(
                    'Cette classe n\'existe pas.',
                    null,
                    [],
                    $input,
                    'schoolClassId',
                    $input->schoolClassId,
                ),
            ]));
        }

        return $schoolClass;
    }

    private function sendNotification(ContactMessage $contactMessage): void
    {
        $settings = $this->settingsProvider->get();
        if (!$settings->contactEmailEnabled) {
            return;
        }

        $recipient = $settings->contactEmail;
        if (null === $recipient || '' === $recipient) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($recipient))
            ->to(new Address($recipient))
            ->replyTo(new Address($contactMessage->getEmail() ?? '', $contactMessage->getName() ?? ''))
            ->subject(sprintf('[Contact] %s', $contactMessage->getSubject() ?? ''))
            ->htmlTemplate('emails/contact_message.html.twig')
            ->context([
                'contact' => $contactMessage,
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            // Already stored: staff can still handle it from EasyAdmin.
            $this->logger->error('Failed to send contact message email.', [
                'exception' => $exception,
                'contactMessageId' => $contactMessage->getId(),
            ]);
        }
    }

    private function normalizeOptional(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}
