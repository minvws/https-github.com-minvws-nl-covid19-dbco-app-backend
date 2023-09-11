<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dateTime = CarbonImmutable::tomorrow()->format('Y-m-d H:m:s');

        DB::statement('UPDATE assignment SET expires_at = ? WHERE expires_at IS NULL AND deleted_at IS NULL', [$dateTime]);
    }

    public function down(): void
    {
    }
};
