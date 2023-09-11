<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class AddMailTemplateDeletedMessage extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->string('mail_language')->after('mail_variant')->default('nl');
            $table->dateTime('deleted_at')->nullable();
        });

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'deleted_message',
            'language' => 'nl',
            'subject' => 'Belpoging van uw GGD betreft COVID-19',
            'body' => $this->deletedMessageBodyNL(),
            'preview' => ' ',
            'summary' => ' ',
            'footer' => $this->footerNL(),
            'secure' => 0,
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'deleted_message',
            'language' => 'en',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->deletedMessageBodyEN(),
            'preview' => ' ',
            'summary' => ' ',
            'footer' => $this->footerEN(),
            'secure' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('mail_language');
        });
    }

    private function deletedMessageBodyNL(): string
    {
        return <<<'HTML'
Beste {{fullname}},

Eerder hebben wij u een bericht gestuurd. Dit bericht is ingetrokken en daarom verwijderd.

Met vriendelijke groet,
HTML;
    }

    private function deletedMessageBodyEN(): string
    {
        return <<<'HTML'
Beste {{fullname}},

Previously we have sent you a message. This message has been withdrawn and therefore deleted.

Met vriendelijke groet,
HTML;
    }

    private function footerNL(): string
    {
        return <<<'HTML'
**Team Infectieziektenbestrijding** - {{ggdRegion}}

Heb je vragen over dit bericht?
Bel ons via {{ggdPhoneNumber}}
HTML;
    }

    private function footerEN(): string
    {
        return <<<'HTML'
**Team Infectious Disease Control** - {{ggdRegion}}

Any questions about this message?
Call us at {{ggdPhoneNumber}}
HTML;
    }
}
