<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_item', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table
                ->foreignUuid('policy_version_uuid')
                ->references('uuid')
                ->on('policy_version')
                ->cascadeOnDelete();
            $table->string('person_type_enum');
            $table->string('calendar_item_enum');
            $table->string('label');
            $table->string('fixed_calendar_item_name_enum')->nullable();
            $table->string('color_enum');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_item');
    }
};
