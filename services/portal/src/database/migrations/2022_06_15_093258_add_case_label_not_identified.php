<?php

declare(strict_types=1);

use App\Repositories\CaseLabelRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Rfc4122\UuidV4;

class AddCaseLabelNotIdentified extends Migration
{
    public function up(): void
    {
        DB::statement(
            '
                INSERT INTO case_label (uuid, code, label, is_selectable, created_at, updated_at)
                VALUES (:uuid, :code, "Niet geïdentificeerd", 0, now(), now());
            ',
            [
                'uuid' => UuidV4::uuid4()->toString(),
                'code' => CaseLabelRepository::CASE_LABEL_CODE_NOT_IDENTIFIED,
            ],
        );
    }

    public function down(): void
    {
        DB::statement('DELETE FROM case_label WHERE code = "not_identified";');
    }
}
