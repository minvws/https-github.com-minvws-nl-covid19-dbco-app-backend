<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function is_null;

class QuestionnaireSeeder extends Seeder
{
    private const ALL_CATEGORIES = '1,2a,2b,3a,3b';
    public const VERSION = 9;

    /**
     * Run the questionnaire seed.
     *
     * To be able to change the questionnaire from the seed without disturbing answers already given,
     * the questionnaire always gets a new uuid upon seed. If you change the seed, don't forget to bump the
     * version number.
     *
     * For populating a local development db, we use a questionnaireUuid that dummy data can refer to
     */
    public function run(): void
    {
        $now = CarbonImmutable::now();

        // Only seed one version of a questionnaire.
        $versionedQuestionnaire = DB::table('questionnaire')
            ->where('version', '=', self::VERSION)
            ->where('task_type', '=', 'contact')
            ->first();

        if (!is_null($versionedQuestionnaire)) {
            return;
        }

        $questionnaireUuid = (string) Str::uuid();

        DB::table('questionnaire')->insert([
            'name' => 'Default questionnaire voor contacten',
            'uuid' => $questionnaireUuid,
            'task_type' => 'contact',
            'version' => self::VERSION,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'category',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'classification',
            'question_type' => 'classificationdetails',
            'label' => 'Vragen over jullie ontmoeting',
            'description' => null,
            'sort_order' => 10,
            'relevant_for_categories' => self::ALL_CATEGORIES,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'contactdetails',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'contactdetails',
            'question_type' => 'contactdetails',
            'label' => 'Contactgegevens',
            'description' => null,
            'sort_order' => 20,
            'relevant_for_categories' => self::ALL_CATEGORIES,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'birthdate',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'contactdetails',
            'question_type' => 'date',
            'label' => 'Geboortedatum',
            'header' => 'Geboortedatum',
            'description' => null,
            'sort_order' => 30,
            'relevant_for_categories' => '1',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'relationship',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'contactdetails',
            'question_type' => 'multiplechoice',
            'label' => 'Wat is deze persoon van je?',
            'header' => 'Relatie tot de index',
            'description' => null,
            'sort_order' => 40,
            'relevant_for_categories' => self::ALL_CATEGORIES,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (Relationship::all() as $i => $relationship) {
            DB::table('answer_option')->insert([
                'uuid' => (string) Str::uuid(),
                'question_uuid' => $questionUuid,
                'label' => $relationship->label,
                'value' => $relationship->value,
                'sort_order' => ((int) $i + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'mention',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'contactdetails',
            'question_type' => 'multiplechoice',
            'label' => 'Mag de GGD jouw naam noemen?',
            'header' => 'GGD Info',
            'description' => 'De GGD kan in sommige situaties je naam gebruiken in communicatie naar je contacten. Zo kunnen zij beter geïnformeerd worden.',
            'sort_order' => 50,
            'relevant_for_categories' => self::ALL_CATEGORIES,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('answer_option')->insert([
            'uuid' => (string) Str::uuid(),
            'question_uuid' => $questionUuid,
            'label' => YesNoUnknown::yes()->label,
            'value' => YesNoUnknown::yes()->value,
            'trigger_name' => 'setShareIndexNameToYes',
            'sort_order' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('answer_option')->insert([
            'uuid' => (string) Str::uuid(),
            'question_uuid' => $questionUuid,
            'label' => YesNoUnknown::no()->label,
            'value' => YesNoUnknown::no()->value,
            'trigger_name' => 'setShareIndexNameToNo',
            'sort_order' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionUuid = (string) Str::uuid();
        DB::table('question')->insert([
            'uuid' => $questionUuid,
            'identifier' => 'remarks',
            'questionnaire_uuid' => $questionnaireUuid,
            'group_name' => 'contactdetails',
            'question_type' => 'open',
            'label' => 'Moet de GGD nog iets weten?',
            'header' => 'GGD Info',
            'description' => 'Bijvoorbeeld: omschrijving van het contact moment',
            'sort_order' => 60,
            'relevant_for_categories' => self::ALL_CATEGORIES,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
