<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Doubled the length of the varchar to make sure the created roles can be stored.
     * The issue arises in EloquentUserFactory.php @ line 20 ($this->faker->numberBetween(1, $existingRoles->count())).
     */
    public function up(): void
    {
        Schema::table('bcouser', static function (Blueprint $table): void {
            $table->string('roles', 510)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bcouser', static function (Blueprint $table): void {
            $table->string('roles', 255)->change();
        });
    }
};
