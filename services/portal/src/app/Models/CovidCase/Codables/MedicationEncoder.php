<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\CovidCase\Medicine;
use App\Models\Versions\CovidCase\Medication\MedicationCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function collect;
use function is_array;
use function sprintf;

class MedicationEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof MedicationCommon);

        $container->hasMedication = $object->hasMedication;

        if ($object->hasMedication === YesNoUnknown::yes() && is_array($object->medicines)) {
            $container->medicines = self::medicinesToArray($object->medicines);
        }

        $container->isImmunoCompromised = $object->isImmunoCompromised;
    }

    private static function medicinesToArray(array $medicines): array
    {
        return collect($medicines)->map(static function (Medicine $medicine) {
            return sprintf(
                '%s %s %s',
                $medicine->name,
                $medicine->remark ?: '',
                $medicine->knownEffects ? '(' . $medicine->knownEffects . ')' : '',
            );
        })->toArray();
    }
}
