<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrganisationOutsourceDepartmentParentOrganisation extends Migration
{
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->uuid('parent_organisation')->nullable();

            $table->foreign('parent_organisation')->references('uuid')
                ->on('organisation')
                ->cascadeOnDelete();
        });

        /** @var EloquentOrganisation|null $sosCedOrganisation */
        $sosCedOrganisation = EloquentOrganisation::where('type', OrganisationType::outsourceOrganisation()->value)
            ->where('external_id', '99004')
            ->first();

        if ($sosCedOrganisation === null) {
            return;
        }

        EloquentOrganisation::where('type', OrganisationType::outsourceDepartment()->value)
            ->update([
                'parent_organisation' => $sosCedOrganisation->uuid,
            ]);
    }

    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropForeign(['parent_organisation']);
            $table->dropColumn(['parent_organisation']);
        });
    }
}
