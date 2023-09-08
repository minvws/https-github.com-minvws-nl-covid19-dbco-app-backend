<?php

declare(strict_types=1);

namespace App\Http\Responses\Intake;

use App\Models\Eloquent\Intake;
use MinVWS\Codable\DateTimeFormatException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class IntakeEncoder implements EncodableDecorator
{
    /**
     * @throws DateTimeFormatException
     */
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof Intake) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->identifier = $value->identifier;

        $container->dateOfBirth->encodeDateTime($value->date_of_birth, 'Y-m-d');
        $container->dateOfSymptomOnset->encodeDateTime($value->date_of_symptom_onset, 'Y-m-d');
        $container->dateOfTest->encodeDateTime($value->date_of_test, 'Y-m-d');

        $container->cat1Count = $value->cat1_count;
        $container->estimatedCat2Count = $value->estimated_cat2_count;

        $this->encodeLabels($value, $container->nestedContainer('labels'));

        $container->priority = $value->priority;

        $container->createdAt = $value->created_at;
        $container->updatedAt = $value->updatedAt;
        $container->receivedAt = $value->received_at;
    }

    private function encodeLabels(Intake $value, EncodingContainer $nestedContainer): void
    {
        $nestedContainer->encodeArray($value->caseLabels()->get()->all());
    }
}
