<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_search', static function (Blueprint $table): void {
            $table->id();
            $table
                ->foreignUuid('task_uuid')
                ->constrained('task', 'uuid')
                ->cascadeOnDelete();
            $table->string('key')->index();
            $table->string('hash', 128)->index();
            $table->unique(['task_uuid', 'key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_searches');
    }
};
