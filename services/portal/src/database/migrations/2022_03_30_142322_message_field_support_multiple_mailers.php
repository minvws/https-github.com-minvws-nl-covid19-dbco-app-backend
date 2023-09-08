<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MessageFieldSupportMultipleMailers extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropForeign('message_bco_user_uuid_foreign');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('smm_from_name');
            $table->dropColumn('smm_from_email');

            $table->renameColumn('mail_variant', 'mail_template');
            $table->renameColumn('bco_user_uuid', 'user_uuid');
            $table->renameColumn('contact_uuid', 'task_uuid');
            $table->renameColumn('smm_uuid', 'mailer_identifier');
            $table->renameColumn('smm_to_name', 'to_name');
            $table->renameColumn('smm_to_email', 'to_email');
            $table->renameColumn('smm_telephone', 'telephone');
            $table->renameColumn('smm_subject', 'subject');
            $table->renameColumn('smm_text', 'text');
            $table->renameColumn('smm_status', 'status');
            $table->renameColumn('smm_notification_sent_at', 'notification_sent_at');
            $table->renameColumn('smm_expires_at', 'expires_at');
            $table->renameColumn('smm_identity_required', 'identity_required');
            $table->renameColumn('smm_pseudo_bsn', 'pseudo_bsn');
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropForeign('message_user_uuid_foreign');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->renameColumn('mail_template', 'mail_variant');
            $table->renameColumn('user_uuid', 'bco_user_uuid');
            $table->renameColumn('task_uuid', 'contact_uuid');
            $table->renameColumn('mailer_identifier', 'smm_uuid');
            $table->renameColumn('to_name', 'smm_to_name');
            $table->renameColumn('to_email', 'smm_to_email');
            $table->renameColumn('telephone', 'smm_telephone');
            $table->renameColumn('subject', 'smm_subject');
            $table->renameColumn('text', 'smm_text');
            $table->renameColumn('status', 'smm_status');
            $table->renameColumn('notification_sent_at', 'smm_notification_sent_at');
            $table->renameColumn('expires_at', 'smm_expires_at');
            $table->renameColumn('identity_required', 'smm_identity_required');
            $table->renameColumn('pseudo_bsn', 'smm_pseudo_bsn');

            $table->string('smm_from_name');
            $table->string('smm_from_email');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->foreign('bco_user_uuid')->references('uuid')->on('bcouser')->onDelete('cascade');
        });
    }
}
