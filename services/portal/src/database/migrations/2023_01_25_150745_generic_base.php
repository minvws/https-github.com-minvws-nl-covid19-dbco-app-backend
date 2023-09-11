<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->string('code', 20);
            $table->string('name', 100);
        });

        Schema::create('disease_model', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('disease_id');
            $table->tinyInteger('version');
            $table->string('status', 10);
            $table->longText('shared_defs')->nullable();
            $table->longText('dossier_schema');
            $table->longText('contact_schema');
            $table->longText('event_schema');
            $table->timestamps();

            $table->foreign('disease_id')
                ->references('id')
                ->on('disease')
                ->cascadeOnDelete();

            $table->unique(['disease_id', 'version'], 'u_disease_model');
        });

        Schema::create('disease_model_ui', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('disease_model_id');
            $table->tinyInteger('version');
            $table->string('status', 10);
            $table->longText('dossier_schema');
            $table->longText('contact_schema');
            $table->longText('event_schema');
            $table->longText('translations')->nullable();
            $table->timestamps();

            $table->foreign('disease_model_id')
                ->references('id')
                ->on('disease_model')
                ->cascadeOnDelete();

            $table->unique(['disease_model_id', 'version'], 'u_disease_model_ui');
        });

        Schema::create('dossier', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('disease_model_id');
            $table->char('organisation_uuid', 36);
            $table->string('identifier', 30);
            $table->timestamps();

            $table->foreign('disease_model_id')
                ->references('id')
                ->on('disease_model')
                ->restrictOnDelete();


            $table->foreign('organisation_uuid')
                ->references('uuid')
                ->on('organisation')
                ->restrictOnDelete();
        });

        Schema::create('dossier_fragment', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('dossier_id');
            $table->string('name', 50);
            $table->longText('data')->charset('latin1')->collation('latin1_bin');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->foreign('dossier_id')
                ->references('id')
                ->on('dossier')
                ->cascadeOnDelete();

            $table->unique(['dossier_id', 'name']);
        });

        Schema::create('dossier_contact', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('dossier_id');
            $table->string('identifier', 30);
            $table->timestamps();

            $table->foreign('dossier_id')
                ->references('id')
                ->on('dossier')
                ->restrictOnDelete();
        });

        Schema::create('dossier_contact_fragment', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('dossier_contact_id');
            $table->string('name', 50);
            $table->longText('data')->charset('latin1')->collation('latin1_bin');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->foreign('dossier_contact_id')
                ->references('id')
                ->on('dossier_contact')
                ->cascadeOnDelete();

            $table->unique(['dossier_contact_id', 'name']);
        });

        Schema::create('dossier_event', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('dossier_id');
            $table->timestamps();

            $table->foreign('dossier_id')
                ->references('id')
                ->on('dossier')
                ->restrictOnDelete();
        });

        Schema::create('dossier_event_fragment', static function (Blueprint $table): void {
            $table->integerIncrements('id');
            $table->unsignedInteger('dossier_event_id');
            $table->string('name', 50);
            $table->longText('data')->charset('latin1')->collation('latin1_bin');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->foreign('dossier_event_id')
                ->references('id')
                ->on('dossier_event')
                ->cascadeOnDelete();

            $table->unique(['dossier_event_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_event_fragment');
        Schema::dropIfExists('dossier_event');
        Schema::dropIfExists('dossier_contact_fragment');
        Schema::dropIfExists('dossier_contact');
        Schema::dropIfExists('dossier_fragment');
        Schema::dropIfExists('dossier');
        Schema::dropIfExists('disease_model_ui');
        Schema::dropIfExists('disease_model');
        Schema::dropIfExists('disease');
    }
};
