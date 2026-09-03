<?php

declare(strict_types=1);

namespace App\Controller\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Markdown body edited with Milkdown Crepe (WYSIWYG) in the admin form.
 * The underlying value remains Markdown, matching the public SPA renderer.
 */
final class MarkdownEditorField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/textarea')
            ->setFormType(TextareaType::class)
            ->addCssClass('field-markdown-editor')
            ->setFormTypeOptions([
                'attr' => [
                    'data-markdown-editor' => '1',
                    'rows' => 16,
                ],
            ])
            ->hideOnIndex();
    }
}
