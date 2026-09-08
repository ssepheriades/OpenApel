<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\AppTimezone;
use App\Controller\Admin\Field\MarkdownEditorField;
use App\Entity\Event;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use App\Enum\MediaMapping;
use App\Service\EventScheduleNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class EventCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EventScheduleNormalizer $scheduleNormalizer,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Event')
            ->setEntityLabelInPlural('Events')
            ->setTimezone(AppTimezone::NAME)
            ->setDefaultSort(['startsAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield TextField::new('slug', 'Adresse publique')
            ->setRequired(false)
            ->setFormTypeOption('empty_data', '')
            ->setHelp('Laissé vide, le champ est généré depuis le titre et l’année. Évitez de le changer une fois publié.');
        yield MarkdownEditorField::new('description', 'Description');
        yield TextField::new('shortDescription');
        yield BooleanField::new('isAllDay', 'Journée entière')
            ->addCssClass('js-event-all-day');
        yield DateTimeField::new('startsAt', 'Début')
            ->setRequired(false)
            ->setFormTypeOption('required', false)
            ->addCssClass('js-event-timed-field');
        yield DateTimeField::new('endsAt', 'Fin')
            ->setRequired(false)
            ->setFormTypeOption('required', false)
            ->addCssClass('js-event-timed-field');
        yield DateField::new('allDayStartsOn', 'Date de début')
            ->setRequired(false)
            ->setFormTypeOption('input', 'datetime_immutable')
            ->onlyOnForms()
            ->addCssClass('js-event-all-day-field');
        yield DateField::new('allDayEndsOn', 'Date de fin')
            ->setHelp('Date de fin inclusive. Laisser vide pour un seul jour.')
            ->setFormTypeOption('input', 'datetime_immutable')
            ->onlyOnForms()
            ->addCssClass('js-event-all-day-field');
        yield TextField::new('location');
        yield UrlField::new('ticketingUrl')->hideOnIndex();
        yield ChoiceField::new('type')
            ->setChoices(
                array_combine(
                    array_map(fn (EventType $t) => $t->label(), EventType::cases()),
                    EventType::cases()
                )
            );
        yield ChoiceField::new('state')
            ->setChoices(
                array_combine(
                    array_map(fn (EventState $s) => $s->label(), EventState::cases()),
                    EventState::cases()
                )
            );
        yield ChoiceField::new('visibility')
            ->setChoices(
                array_combine(
                    array_map(fn (EventVisibility $v) => $v->label(), EventVisibility::cases()),
                    EventVisibility::cases()
                )
            );
        yield AssociationField::new('grades', 'Niveaux')
            ->setHelp('Laisser vide pour toute l’école. Ne pas combiner avec des classes.')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();
        yield AssociationField::new('schoolClasses', 'Classes')
            ->setHelp('Laisser vide pour toute l’école. Ne pas combiner avec des niveaux.')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();
        yield ImageField::new('heroImageFilename', 'Hero')
            ->setBasePath(MediaMapping::Photos->uriPrefix())
            ->onlyOnIndex();
        yield Field::new('heroImageFile', 'Hero')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => false])
            ->onlyOnForms();
        yield ImageField::new('flyerImageFilename', 'Flyer')
            ->setBasePath(MediaMapping::Photos->uriPrefix())
            ->onlyOnIndex();
        yield Field::new('flyerImageFile', 'Flyer')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false, 'image_uri' => false])
            ->onlyOnForms();
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        return $this->bindAllDaySchedule(parent::createNewFormBuilder($entityDto, $formOptions, $context));
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        return $this->bindAllDaySchedule(parent::createEditFormBuilder($entityDto, $formOptions, $context));
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Event) {
            $this->scheduleNormalizer->normalize($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Event) {
            $this->scheduleNormalizer->normalize($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function bindAllDaySchedule(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $submitted = $event->getData();
            if (!\is_array($submitted) || !$this->isSubmittedAllDay($submitted)) {
                return;
            }

            $start = $submitted['allDayStartsOn'] ?? null;
            $end = $submitted['allDayEndsOn'] ?? null;

            if (\is_string($start) && '' !== $start) {
                $submitted['startsAt'] = $this->dateToDateTimeValue($start);
            }

            $submitted['endsAt'] = \is_string($end) && '' !== $end
                ? $this->dateToDateTimeValue($end)
                : '';

            $event->setData($submitted);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!$data instanceof Event) {
                return;
            }

            $form = $event->getForm();
            if (!$data->isAllDay()) {
                $this->scheduleNormalizer->normalize($data);

                return;
            }

            $start = $form->has('allDayStartsOn') ? $form->get('allDayStartsOn')->getData() : null;
            $end = $form->has('allDayEndsOn') ? $form->get('allDayEndsOn')->getData() : null;

            if ($start instanceof \DateTimeInterface) {
                $data->setStartsAt(\DateTimeImmutable::createFromInterface($start));
            }

            $data->setEndsAt($end instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($end) : null);
            $this->scheduleNormalizer->normalize($data);
        });

        return $builder;
    }

    /**
     * @param array<string, mixed> $submitted
     */
    private function isSubmittedAllDay(array $submitted): bool
    {
        $value = $submitted['isAllDay'] ?? false;
        if (\is_bool($value)) {
            return $value;
        }

        return \in_array((string) $value, ['1', 'true', 'on'], true);
    }

    private function dateToDateTimeValue(string $date): string
    {
        if (str_contains($date, 'T') || str_contains($date, ' ')) {
            return $date;
        }

        return $date.'T00:00:00';
    }
}
