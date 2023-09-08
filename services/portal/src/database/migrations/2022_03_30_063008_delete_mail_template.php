<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DeleteMailTemplate extends Migration
{
    public function up(): void
    {
        Schema::drop('mail_template');
    }

    public function down(): void
    {
        // not supported
    }
}
