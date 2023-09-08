<?php

declare(strict_types=1);

namespace App\Services\Dossier;

use App\Models\Disease\Disease;
use App\Models\Dossier\Contact;
use App\Models\Dossier\Dossier;
use App\Repositories\Dossier\ContactRepository;
use App\Schema\Validation\ValidationResult;
use App\Services\BcoNumber\BcoNumberService;
use App\Services\Disease\DiseaseSchemaService;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;

use function array_replace_recursive;
use function assert;
use function filter_var;
use function is_array;
use function is_int;
use function is_object;

use const FILTER_VALIDATE_INT;

class ContactService
{
    public function __construct(private readonly ContactRepository $contactRepository, private readonly DiseaseSchemaService $diseaseSchemaService, private readonly BcoNumberService $bcoNumberService)
    {
    }

    public function getContact(string|int $id): ?Contact
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($filteredId)) {
            return null;
        }

        return $this->contactRepository->getContact($filteredId);
    }

    public function makeContact(Dossier $dossier): Contact
    {
        return $this->contactRepository->makeContact($dossier);
    }

    public function validateContact(Dossier $dossier, ?Contact $contact, ?array $data, ?string $view): ValidationResult
    {
        $schemaVersion = $contact?->getSchemaVersion() ?? $this->diseaseSchemaService->getContactSchema(
            $dossier->diseaseModel,
        )->getCurrentVersion();

        $allData = [];

        if ($contact?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(Dossier::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($contact);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeContact(Contact $contact, ?string $view): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->setView($view);
        $encoder->getContext()->registerDecorator(Disease::class, $contact->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($contact);
        assert(is_object($data));
        return $data;
    }

    public function decodeContact(Contact $contact, array $data, ?string $view): Contact
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->setView($view);
        $decoder->getContext()->registerDecorator(Contact::class, $contact->getSchemaVersion()->getDecodableDecorator());
        $decoder->decode($data)->decodeObject(Contact::class, $contact);
        return $contact;
    }

    public function saveContact(Contact $contact): void
    {
        if (!$contact->exists) {
            $contact->identifier = $this->bcoNumberService->makeUniqueNumber()->bco_number;
        }

        $this->contactRepository->saveContact($contact);
    }

    public function deleteContact(Contact $contact): void
    {
        $this->contactRepository->deleteContact($contact);
    }
}
