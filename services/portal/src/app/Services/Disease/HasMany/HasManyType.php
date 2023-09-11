<?php

declare(strict_types=1);

namespace App\Services\Disease\HasMany;

use App\Models\Dossier\Contact;
use App\Models\Dossier\Dossier;
use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function explode;
use function is_iterable;
use function is_null;
use function route;

class HasManyType extends Type
{
    public const VIEW_LIST = 'list';

    public function __construct(public readonly Schema $schema, public readonly array $listProperties)
    {
        parent::__construct();

        self::setListViewForFields($this->schema, $this->listProperties);
    }

    private static function setListViewForFields(Schema $schema, array $listProperties): void
    {
        foreach ($listProperties as $listProperty) {
            $parts = explode('.', $listProperty);
            $current = $schema->getCurrentVersion();
            foreach ($parts as $part) {
                $field = $current->getField($part);
                if ($field === null) {
                    break;
                }

                $field->addToView(self::VIEW_LIST);

                $type = $field->getType();
                if ($type instanceof ArrayType && $type->getElementType() instanceof SchemaType) {
                    $elementType = $type->getElementType();
                    assert($elementType instanceof SchemaType);
                    $current = $elementType->getSchemaVersion();
                } elseif ($type instanceof SchemaType) {
                    $current = $type->getSchemaVersion();
                } else {
                    $current = null;
                }

                if ($current === null) {
                    break;
                }
            }
        }
    }

    public function encode(EncodingContainer $container, mixed $value): void
    {
        assert(is_null($value) || is_iterable($value));

        $source = $container->getContext()->getValue(SchemaObject::class);

        $container->getContext()->setView(self::VIEW_LIST);

        $container->data->encodeArray(
            $value,
            fn (EncodingContainer $elementContainer, $elementValue) =>
                $this->encodeElement($elementContainer, $elementValue)
        );

        if (!$source instanceof Dossier) {
            return;
        }

        $container->links->self->href = route('api-contact-index', ['dossier' => $source, 'view' => self::VIEW_LIST]);

        $container->links->create->href = route('api-contact-create', ['dossier' => $source, 'view' => self::VIEW_LIST]);
        $container->links->create->method = 'POST';

        $container->links->createModal->href = route('api-contact-new', ['dossier' => $source]);
    }

    private function encodeElement(EncodingContainer $container, mixed $value): void
    {
        assert($value instanceof Contact);
        $this->schema->getCurrentVersion()->encode($container->data, $value);

        $container->links->self->href = route('api-contact-show', ['dossierContact' => $value, 'view' => self::VIEW_LIST]);
        $container->links->full->href = route('api-contact-show', ['dossierContact' => $value]);

        $container->links->update->href = route('api-contact-update', ['dossierContact' => $value, 'view' => self::VIEW_LIST]);
        $container->links->update->method = 'PUT';

        $container->links->delete->href = route('api-contact-delete', ['dossierContact' => $value]);
        $container->links->delete->method = 'DELETE';

        $container->links->editModal->href = route('api-contact-show', ['dossierContact' => $value]);
    }

    public function isOfType(mixed $value): bool
    {
        return $value instanceof HasManyType && $value->schema === $this->schema;
    }

    public function getAnnotationType(): string
    {
        return 'any';
    }

    public function getTypeScriptAnnotationType(): string
    {
        return 'any';
    }

    public function toJSONSchema(Context $context): array
    {
        return [];
    }
}
