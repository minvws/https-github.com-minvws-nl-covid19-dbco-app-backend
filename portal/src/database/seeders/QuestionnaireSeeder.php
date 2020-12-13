<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class QuestionnaireSeeder extends Seeder
{
    private const ALL_CATEGORIES = '1,2a,2b,3';
    private const VERSION = 1;

    /**
     * Run the questionnaire seed.
     *
     * To be able to change the questionnaire from the seed without disturbing answers already given,
     * the questionnaire always gets a new uuid upon seed. If you change the seed, don't forget to up the
     * version number.
     *
     * For populating a local development db, we use a questionnaireUuid that dummy data can refer to
     *
     * @return void
     */
    public function run()
    {

        $now = Date::now();

        $questionnaireUuid = (string)Str::uuid();

        if (App::environment() == 'development') {
            // in development we fixate the questionnaire uuid so the dummy data knows
            // what questionnaire to use
            $questionnaireUuid = 'facade01-feed-dead-c0de-defacedc0c0a';
        }

        // Only seed one version of a questionnaire.
        $versionedQuestionnaire = DB::table('questionnaire')
            ->where('version', '=', self::VERSION)
            ->where('task_type', '=', 'contact')
            ->first();

        if (is_null($versionedQuestionnaire)) {

            DB::table('questionnaire')->insert([
                'name' => 'Default questionnaire voor contacten',
                'uuid' => $questionnaireUuid,
                'task_type' => 'contact',
                'version' => self::VERSION,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $questionUuid = (string)Str::uuid();
            DB::table('question')->insert([
                'uuid' => $questionUuid,
                'questionnaire_uuid' => $questionnaireUuid,
                'group_name' => 'classification',
                'question_type' => 'classificationdetails',
                'label' => 'Vragen over jullie ontmoeting',
                'description' => null,
                'sort_order' => 10,
                'relevant_for_categories' => QuestionnaireSeeder::ALL_CATEGORIES,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $questionUuid = (string)Str::uuid();
            DB::table('question')->insert([
                'uuid' => $questionUuid,
                'questionnaire_uuid' => $questionnaireUuid,
                'group_name' => 'contactdetails',
                'question_type' => 'contactdetails',
                'label' => 'Contactgegevens',
                'description' => null,
                'sort_order' => 20,
                'relevant_for_categories' => QuestionnaireSeeder::ALL_CATEGORIES,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $questionUuid = (string)Str::uuid();
            DB::table('question')->insert([
                'uuid' => $questionUuid,
                'questionnaire_uuid' => $questionnaireUuid,
                'group_name' => 'contactdetails',
                'question_type' => 'date',
                'label' => 'Geboortedatum',
                'header' => 'Geboortedatum',
                'description' => null,
                'sort_order' => 30,
                'relevant_for_categories' => '1',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $questionUuid = (string)Str::uuid();
            DB::table('question')->insert([
                'uuid' => $questionUuid,
                'questionnaire_uuid' => $questionnaireUuid,
                'group_name' => 'contactdetails',
                'question_type' => 'multiplechoice',
                'label' => 'Waar ken je deze persoon van?',
                'header' => 'Relatie tot de index',
                'description' => null,
                'sort_order' => 40,
                'relevant_for_categories' => '2a,2b',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            DB::table('answer_option')->insert([[
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Ouder',
                'value' => 'Ouder',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Kind',
                'value' => 'Kind',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Broer of zus',
                'value' => 'Broer of zus',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Partner',
                'value' => 'Partner',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Familielid (overig)',
                'value' => 'Familielid (overig)',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Huisgenoot',
                'value' => 'Huisgenoot',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Vriend of kennis',
                'value' => 'Vriend of kennis',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Medestudent of leerling',
                'value' => 'Medestudent of leerling',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Collega',
                'value' => 'Collega',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Gezondheidszorg medewerker',
                'value' => 'Gezondheidszorg medewerker',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Ex-partner',
                'value' => 'Ex-partner',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Overig',
                'value' => 'Overig',
                'created_at' => $now,
                'updated_at' => $now
            ]]);

            $questionUuid = (string)Str::uuid();
            DB::table('question')->insert([
                'uuid' => $questionUuid,
                'questionnaire_uuid' => $questionnaireUuid,
                'group_name' => 'contactdetails',
                'question_type' => 'multiplechoice',
                'label' => 'Geldt één of meer van deze dingen voor deze persoon?',
                'header' => 'Prioriteit',
                'description' => "<p>Voor deze groepen is het extra belangrijk dat we ze snel informeren en het juiste advies geven.</p>" .
                    "<ul><li>Tussen de 15 en 29 jaar</li>" .
                    "<li>55 jaar of ouder</li>" .
                    "<li>Gezondheidsklachten of extra gezondheidsrisico's</li>" .
                    "<li>Woont in een zorginstelling of asielzoekerscentrum (bijvoorbeeld bejaardentehuis)</li>" .
                    "<li>Gaat naar school of kinderopvang</li>" .
                    "<li>Werkt in de zorg, onderwijs of kinderopvang</li>" .
                    "<li>Heeft een ander contactberoep (bijvoorbeeld kapper)</li></ul>",
                'sort_order' => 50,
                'relevant_for_categories' => '1,2a,2b',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            DB::table('answer_option')->insert([[
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Ja, één of meer',
                'value' => 'Ja',
                'trigger_name' => 'communication_staff',
                'created_at' => $now,
                'updated_at' => $now
            ], [
                'uuid' => (string)Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => 'Nee, ik denk het niet',
                'value' => 'Nee',
                'trigger_name' => 'communication_index',
                'created_at' => $now,
                'updated_at' => $now
            ]]);
        }
    }
}
