<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class AddIntakeLabels extends Migration
{
    public function up(): void
    {
        DB::raw("UPDATE `case_label` SET `code` = 'sample' WHERE `label` = 'Steekproef';");

        Schema::table('case_label', static function (Blueprint $table): void {
            $table->boolean('is_selectable')->default(true)->after('label');
            $table->string('code')->nullable(false)->change();
        });

        DB::raw(
            sprintf(
                "INSERT IGNORE into case_label (`uuid`, `code`, `label`, `is_selectable`) VALUES('%s', 'intake_submitted', 'Intake ingevuld', 0)",
                Uuid::uuid4(),
            ),
        );
        DB::raw(
            sprintf(
                "INSERT IGNORE into case_label (`uuid`, `code`, `label`, `is_selectable`) VALUES('%s', 'intake_invited', 'Uitgenodigd voor intake', 0)",
                Uuid::uuid4(),
            ),
        );
    }

    public function down(): void
    {
        Schema::table('case_label', static function (Blueprint $table): void {
            $table->dropColumn('is_selectable');
            $table->string('code')->nullable(true)->change();
        });

        DB::raw("DELETE FROM case_label WHERE `code` = 'intake_submitted'");
        DB::raw("DELETE FROM case_label WHERE `code` = 'intake_invited'");
    }
}
