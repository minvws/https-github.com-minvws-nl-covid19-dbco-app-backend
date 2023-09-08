<?php

declare(strict_types=1);

use App\Helpers\SearchableHash;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Services\CaseFragmentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

final class SeedSearchHashCovidcaseTable extends Migration
{
    public function up(): void
    {
        /** @var CaseFragmentService $caseFragmentService */
        $caseFragmentService = app(CaseFragmentService::class);
        /** @var SearchableHash $searchableHash */
        $searchableHash = app(SearchableHash::class);

        DB::table('covidcase')->orderBy('created_at')->chunk(
            100,
            static function ($cases) use ($caseFragmentService, $searchableHash): void {
                foreach ($cases as $case) {
                    $fragments = $caseFragmentService->loadFragments($case->uuid, ['index', 'contact'], true);
                    /** @var Contact $contact */
                    $contact = $fragments['contact'];
                    /** @var Index $index */
                    $index = $fragments['index'];

                    $updates = [];
                    if ($index->lastname && $index->dateOfBirth) {
                        $updates['search_date_of_birth'] = $searchableHash->hashForLastNameAndDateOfBirth(
                            $index->lastname,
                            $index->dateOfBirth,
                        );
                    }

                    if ($index->lastname && $contact->email) {
                        $updates['search_email'] = $searchableHash->hashForLastNameAndEmail($index->lastname, $contact->email);
                    }

                    if ($index->lastname && $contact->phone) {
                        $updates['search_phone'] = $searchableHash->hashForLastNameAndPhone($index->lastname, $contact->phone);
                    }

                    if (empty($updates)) {
                        continue;
                    }

                    DB::table('covidcase')
                        ->where('uuid', $case->uuid)
                        ->update($updates);
                }
            },
        );
    }

    public function down(): void
    {
        // Nothing to do here
    }
}
