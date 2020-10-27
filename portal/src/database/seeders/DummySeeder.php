<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;
use Monolog\DateTimeImmutable;

class DummySeeder extends Seeder
{
    /**
     * Run the dummy seed.
     *
     * @return void
     */
    public function run()
    {
        $now = Date::now();
        $questionnaireUuid = 'facade01-feed-dead-c0de-defacedc0c0a';

        $caseUuid = (string)Str::uuid();
        // Create a case for the dummy user (id 0), with tasks. Case is open, not yet submitted.
        DB::table('case')->insert([
            'name' => 'Bruce Wayne',
            'uuid' => $caseUuid,
            'owner' => 0,
            'date_of_symptom_onset' => date('Y-m-d'),
            'status' => 'open',
            'case_id' => 'GOTHAM001',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('task')->insert([[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Robin',
            'task_context' => 'Business partner',
            'category' => '2a',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Alfred',
            'task_context' => 'Butler',
            'category' => '1',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Batman',
            'task_context' => 'Unclear relationship (never in same room)',
            'category' => '3',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'index',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Catwoman',
            'task_context' => 'Friend',
            'category' => '2b',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ]]);

        $caseUuid = (string)Str::uuid();
        // Create another case for the dummy user (id 0), with tasks. Case is open, not yet closed.
        DB::table('case')->insert([
            'name' => 'Clark Kent',
            'uuid' => $caseUuid,
            'owner' => 0,
            'date_of_symptom_onset' => date('Y-m-d'),
            'status' => 'open',
            'case_id' => 'METROPOLIS001',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $taskUuidLex = (string)Str::uuid();
        $taskUuidLois = (string)Str::uuid();

        DB::table('task')->insert([[
            'uuid' => $taskUuidLex,
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Lex L.',
            'task_context' => 'Arch enemy',
            'category' => '2b',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'questionnaire_uuid' => $questionnaireUuid,
            'created_at' => $now,
            'updated_at' => $now,
        ],[
            'uuid' => $taskUuidLois,
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Lois L.',
            'task_context' => "It's complicated",
            'category' => '1',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'questionnaire_uuid' => $questionnaireUuid,
            'created_at' => $now,
            'updated_at' => $now
        ]]);

        $questions = DB::table('question')
            ->where('questionnaire_uuid', '=', $questionnaireUuid)->get();

        foreach ($questions as $question) {
            if ($question->question_type == 'contactdetails') {
                $contactQuestionUuid = (string)$question->uuid;
            } else if ($question->question_type == 'classificationdetails') {
                $classificationQuestionUuid = (string)$question->uuid;
            } else if ($question->label == 'Geboortedatum') {
                $birthdateQuestionUuid = (string)$question->uuid;
            }
        }

        DB::table('answer')->insert([[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLex,
            'question_uuid' => $classificationQuestionUuid,
            'cfd_livedtogetherrisk' => '0',
            'cfd_durationrisk' => '1',
            'cfd_distancerisk' => '1',
            'cfd_otherrisk' => '1',
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLois,
            'question_uuid' => $classificationQuestionUuid,
            'cfd_livedtogetherrisk' => '1',
            'cfd_durationrisk' => '1',
            'cfd_distancerisk' => '1',
            'cfd_otherrisk' => '0',
            'created_at' => $now,
            'updated_at' => $now
        ]]);

        DB::table('answer')->insert([[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLex,
            'question_uuid' => $contactQuestionUuid,
            'ctd_firstname' => 'Lex',
            'ctd_lastname' => 'Luthor',
            'ctd_email' => 'lex@luthor.dc',
            'ctd_phonenumber' => '0612345678',
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLois,
            'question_uuid' => $contactQuestionUuid,
            'ctd_firstname' => 'Lois',
            'ctd_lastname' => 'Lane',
            'ctd_email' => 'lane.lois@dailyplanet.dc',
            'ctd_phonenumber' => '06987654321',
            'created_at' => $now,
            'updated_at' => $now
        ]]);

        DB::table('answer')->insert([[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLois,
            'question_uuid' => $birthdateQuestionUuid,
            'spv_value' => Date('1976-10-12'),
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'task_uuid' => $taskUuidLex,
            'question_uuid' => $birthdateQuestionUuid,
            'spv_value' => Date('1970-10-11'),
            'created_at' => $now,
            'updated_at' => $now
        ]]);

        $caseUuid = (string)Str::uuid();
        // Create a final case for the dummy user (id 0), some tasks. Case is closed by ggdd.
        DB::table('case')->insert([
            'name' => 'Carol Danvers',
            'uuid' => $caseUuid,
            'owner' => 0,
            'date_of_symptom_onset' => date('Y-m-d'),
            'status' => 'closed',
            'case_id' => 'ASGARD001',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('task')->insert([[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Steve R.',
            'task_context' => 'Ally',
            'category' => '2a',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'ggd',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ],[
            'uuid' => (string)Str::uuid(),
            'case_uuid' => $caseUuid,
            'task_type' => 'contact',
            'source' => 'portal',
            'label' => 'Nick F.',
            'task_context' => "Discovered by",
            'category' => '3',
            'date_of_last_exposure' => date('Y-m-d'),
            'communication' => 'index',
            'informed_by_index' => false,
            'created_at' => $now,
            'updated_at' => $now
        ]]);
    }
}
