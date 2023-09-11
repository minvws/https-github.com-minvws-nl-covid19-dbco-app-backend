<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('bco_user_uuid');
            $table->uuid('case_uuid');
            $table->uuid('contact_uuid')->nullable();
            $table->string('mail_variant');
            $table->uuid('smm_uuid');
            $table->string('smm_from_name');
            $table->string('smm_from_email');
            $table->string('smm_to_name');
            $table->string('smm_to_email');
            $table->string('smm_telephone')->nullable();
            $table->string('smm_subject');
            $table->text('smm_summary')->nullable();
            $table->text('smm_preview');
            $table->longText('smm_text');
            $table->text('smm_footer');
            $table->string('smm_status');
            $table->dateTime('smm_notification_sent_at')->nullable();
            $table->timestamps();

            $table->index('smm_uuid');
            $table->index('smm_status');

            $table->foreign('bco_user_uuid')->references('uuid')
                ->on('bcouser')
                ->onDelete('cascade');

            $table->foreign('case_uuid')->references('uuid')
                ->on('covidcase')
                ->onDelete('cascade');

            $table->foreign('contact_uuid')->references('uuid')
                ->on('task')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message');
    }
}
