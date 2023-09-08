<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCovidcaseSearchTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('covidcase_search', static function (Blueprint $table): void {
            $table->id();
            $table
                ->foreignUuid('covidcase_uuid')
                ->constrained('covidcase', 'uuid')
                ->cascadeOnDelete();
            $table->string('key')->index();
            $table->string('hash', 128)->index();
            $table->unique(['covidcase_uuid', 'key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('covidcase_search');
    }
}
