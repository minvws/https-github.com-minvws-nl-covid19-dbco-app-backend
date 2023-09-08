<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /** @var Collection<int, stdClass> $organisations */
        $organisations = DB::table('organisation')->select('uuid')->get();
        $caseLabelUuid = DB::table('case_label')
            ->where(['code' => 'not_identified'])
            ->value('uuid');

        if ($caseLabelUuid === null) {
            throw new RuntimeException('Could not find case label with code: "not_identified"');
        }

        DB::transaction(static function () use ($organisations, $caseLabelUuid): void {
            foreach ($organisations as $organisation) {
                $query = DB::table('case_label_organisation')->where([
                    'case_label_uuid' => $caseLabelUuid,
                    'organisation_uuid' => $organisation->uuid,
                ]);
                if ($query->exists()) {
                    continue;
                }

                $query->insert([
                    'case_label_uuid' => $caseLabelUuid,
                    'organisation_uuid' => $organisation->uuid,
                    'sortorder' => 210,
                ]);
            }
        });
    }

    public function down(): void
    {
        /** @var Collection<int, stdClass> $organisations */
        $organisations = DB::table('organisation')->select('uuid')->get();
        $caseLabelUuid = DB::table('case_label')
            ->where(['code' => 'not_identified'])
            ->value('uuid');

        if ($caseLabelUuid === null) {
            throw new RuntimeException('Could not find case label with code: "not_identified"');
        }

        DB::transaction(
            static function () use ($caseLabelUuid, $organisations): void {
                foreach ($organisations as $organisation) {
                    DB::table('case_label_organisation')->where([
                        'case_label_uuid' => $caseLabelUuid,
                        'organisation_uuid' => $organisation->uuid,
                    ])
                        ->delete();
                }
            },
        );
    }
};
