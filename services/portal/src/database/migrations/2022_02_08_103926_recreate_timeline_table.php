<?php

declare(strict_types=1);

use App\Models\Eloquent\Note;
use App\Models\Eloquent\Timeline;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class RecreateTimelineTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // There was an old version of this migration, which was faulty.
        // This will result in data loss of all assignment history since the previous migration was run.
        // But it is not desirable to run the old one and then repair the data in a subsequent migration on prod.

        Schema::dropIfExists('timeline');

        Schema::create('timeline', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid')->index();
            $table->uuidMorphs('timelineable');
            $table->timestamps();
        });

        $output = app(ConsoleOutput::class);

        $chunkSize = 200;

        $bar = new ProgressBar($output, (int) ceil(Note::doesntHave('timeline')->count() / $chunkSize));
        $bar->start();

        do {
            $notes = Note::doesntHave('timeline')->limit($chunkSize)->get();

            $inserts = [];
            foreach ($notes as $note) {
                $inserts[] = [
                    'uuid' => Uuid::uuid4()->toString(),
                    'case_uuid' => $note->case_uuid,
                    'timelineable_type' => 'note',
                    'timelineable_id' => $note->uuid,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->created_at,
                ];
            }

            Timeline::insert($inserts);

            $bar->advance();
        } while (Note::doesntHave('timeline')->count() > 0);

        $bar->finish();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline');
    }
}
