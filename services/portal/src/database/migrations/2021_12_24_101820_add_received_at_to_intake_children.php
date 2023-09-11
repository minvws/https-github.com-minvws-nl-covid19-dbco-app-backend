<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReceivedAtToIntakeChildren extends Migration
{
    public function up(): void
    {
        Schema::table('intake_fragment', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(true)->after('version');
        });

        Schema::table('intake_contact', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(true)->after('intake_uuid');
        });

        Schema::table('intake_contact_fragment', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(true)->after('version');
        });

        DB::update(
            "UPDATE intake_fragment SET received_at = (SELECT received_at FROM intake WHERE intake.uuid = intake_fragment.intake_uuid)",
        );
        DB::update(
            "UPDATE intake_contact SET received_at = (SELECT received_at FROM intake WHERE intake.uuid = intake_contact.intake_uuid)",
        );
        DB::update(
            "UPDATE intake_contact_fragment SET received_at = (SELECT received_at FROM intake_contact WHERE intake_contact.uuid = intake_contact_fragment.intake_contact_uuid)",
        );

        Schema::table('intake_fragment', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(false)->after('version')->change();
        });

        Schema::table('intake_contact', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(false)->after('intake_uuid')->change();
        });

        Schema::table('intake_contact_fragment', static function (Blueprint $table): void {
            $table->dateTime('received_at')->nullable(false)->after('version')->change();
        });
    }

    public function down(): void
    {
    }
}
