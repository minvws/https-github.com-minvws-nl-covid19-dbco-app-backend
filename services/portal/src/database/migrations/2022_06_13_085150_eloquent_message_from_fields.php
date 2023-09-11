<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EloquentMessageFromFields extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->string('from_name')->after('mailer_identifier')->nullable();
            $table->string('from_email')->after('from_name')->nullable();
        });

        DB::table('message')->update([
            'from_name' => 'GGD Contact',
            'from_email' => 'noreply@ggdcontact.nl',
        ]);

        Schema::table('message', static function (Blueprint $table): void {
            $table->string('from_name')->nullable(false)->change();
            $table->string('from_email')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('from_name');
            $table->dropColumn('from_email');
        });
    }
}
