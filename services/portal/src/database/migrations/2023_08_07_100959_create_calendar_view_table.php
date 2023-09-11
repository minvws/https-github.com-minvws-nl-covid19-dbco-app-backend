<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_view', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table
                ->foreignUuid('policy_version_uuid')
                ->references('uuid')
                ->on('policy_version')
                ->cascadeOnDelete();
            $table->string('label');
            $table->string('calendar_view_enum');
            $table->timestamps();
        });

        /** Pivot table */
        Schema::create('calendar_view_calendar_item', static function (Blueprint $table): void {
            $table->uuid('calendar_view_uuid');
            $table->uuid('calendar_item_uuid');
        });

        DB::statement("ALTER TABLE calendar_view_calendar_item ADD PRIMARY KEY (calendar_view_uuid, calendar_item_uuid);");

        DB::statement(
            'ALTER TABLE calendar_view_calendar_item ADD CONSTRAINT calendar_view_uuid_foreign FOREIGN KEY (calendar_view_uuid) REFERENCES calendar_view (uuid) ON DELETE CASCADE',
        );
        DB::statement(
            'ALTER TABLE calendar_view_calendar_item ADD CONSTRAINT calendar_item_uuid_foreign FOREIGN KEY (calendar_item_uuid) REFERENCES calendar_item (uuid) ON DELETE CASCADE',
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_view_calendar_item');
        Schema::dropIfExists('calendar_view');
    }
};
