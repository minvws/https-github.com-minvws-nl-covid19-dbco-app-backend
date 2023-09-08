<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->foreignUuid('policy_guideline_uuid')->nullable()->change();
        });
    }
};
