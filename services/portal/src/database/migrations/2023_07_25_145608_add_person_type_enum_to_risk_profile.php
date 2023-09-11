<?php

declare(strict_types=1);

use App\Models\Policy\RiskProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->string('person_type_enum')->nullable()->after('name');
        });

        RiskProfile::query()->update(['person_type_enum' => PolicyPersonType::index()->value]);

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->string('person_type_enum')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->dropColumn('person_type_enum');
        });
    }
};
