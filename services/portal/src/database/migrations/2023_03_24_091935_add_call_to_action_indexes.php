<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chore', static function (Blueprint $table): void {
            $table->index('owner_resource_type', 'chore_owner_resource_type_index');
            $table->index('expires_at', 'chore_expires_at_index');
            $table->index(['owner_resource_type', 'uuid'], 'chore_owner_resource_type_and_uuid_index');
        });

        Schema::table('assignment', static function (Blueprint $table): void {
            $table->index('expires_at', 'assignment_expires_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('chore', static function (Blueprint $table): void {
            $table->dropIndex('chore_owner_resource_type_index');
            $table->dropIndex('chore_expires_at_index');
            $table->dropIndex('chore_owner_resource_type_and_uuid_index');
        });

        Schema::table('assignment', static function (Blueprint $table): void {
            $table->dropIndex('assignment_expires_at_index');
        });
    }
};
