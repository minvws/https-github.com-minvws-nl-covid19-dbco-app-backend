<?php

declare(strict_types=1);

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\Place;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

class UpdatePlacesPostalcode extends Migration
{
    public function up(): void
    {
        $places = Place::all(['uuid', 'postalcode']);

        foreach ($places as $place) {
            if ($place->postalcode === null) {
                continue;
            }

            $place->postalcode = PostalCodeHelper::normalize($place->postalcode);

            try {
                $place->save();
            } catch (Throwable $exception) {
                Log::info('place save failed during postalcode update', ['uuid' => $place->uuid]);
            }
        }
    }

    public function down(): void
    {
        // no return possible
    }
}
