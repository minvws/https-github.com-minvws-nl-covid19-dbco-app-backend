<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContextRelationshipAsString extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->string('relationship')->nullable()->after('relationship_uuid');
        });

        $mapping = [
            'Medewerker' => 'staff',
            'Bezoeker' => 'visitor',
            'Bewoner' => 'resident',
            'Patiënt' => 'patient',
            'Docent' => 'teacher',
            'Student / Leerling' => 'student',
        ];

        foreach ($mapping as $label => $code) {
            DB::update("
                UPDATE context
                SET relationship = :code
                WHERE relationship_uuid = (SELECT r.uuid FROM relationship r WHERE r.label = :label)
            ", ['label' => $label, 'code' => $code]);
        }

        Schema::table('context', static function (Blueprint $table): void {
            $table->dropForeign(['relationship_uuid']);
            $table->dropColumn('relationship_uuid');
        });

        Schema::drop('relationship');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // not supported :/
    }
}
