<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMigrationMess extends Migration
{
    public function up(): void
    {
        // There have been three versions of the same migration
        // (2021_01_26_000000) in the field because it was adjusted during a period
        // after initial creation/deployment. (it was modified in 3 separate PRs)
        // This patch try to retro-actively correct environments that have been patched
        // with the older versions of the original patch.

        Schema::table('category', static function (Blueprint $table): void {
            $table->string('code')->nullable(false)->change();

            if (Schema::hasColumn('category', 'sort_order')) {
                return;
            }

            $table->unsignedInteger('sort_order')->default(0);
            $table->index('sort_order');
        });

        Schema::table('place', function (Blueprint $table): void {
            $table->string('street')->nullable()->change();
            $table->string('housenumber')->nullable()->change();
            $table->string('housenumber_suffix')->nullable()->change();
            $table->string('postalcode', 6)->nullable()->change();
            $table->string('town')->nullable()->change();
            $table->string('country', 2)->default('NL')->change();

            $table->uuid('category_uuid')->nullable()->change();
            // recreate the fk index without destroying the data is done by using the dropForeign/foreign combination
            // This leaves the underlying column intact.
            $this->dropForeignIfExists($table, 'category_uuid');
            $table->foreign('category_uuid')
                ->references('uuid')
                ->on('category')
                ->onDelete('set null');
        });

        Schema::table('place_reference', function (Blueprint $table): void {
            $this->dropForeignIfExists($table, 'place_uuid');
            $table->foreign('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();
        });

        Schema::table('section', function (Blueprint $table): void {
            $this->dropForeignIfExists($table, 'place_uuid');
            $table->foreign('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();

            if (!Schema::hasColumn('section', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('context_section', function (Blueprint $table): void {
            $this->dropForeignIfExists($table, 'context_uuid');
            $this->dropForeignIfExists($table, 'section_uuid');
            $table->foreign('context_uuid')
                ->references('uuid')
                ->on('context')
                ->cascadeOnDelete();

            $table->foreign('section_uuid')
                ->references('uuid')
                ->on('section')
                ->cascadeOnDelete();
        });

        Schema::table('moment', function (Blueprint $table): void {
            $this->dropForeignIfExists($table, 'context_uuid');
            $table->foreign('context_uuid')
                ->references('uuid')
                ->on('context')
                ->cascadeOnDelete();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });

        Schema::table('relationship', static function (Blueprint $table): void {
            if (!Schema::hasColumn('relationship', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
                $table->index('sort_order');
            }
        });

        Schema::table('context', function (Blueprint $table): void {
            $this->dropForeignIfExists($table, 'place_uuid');
            $this->dropForeignIfExists($table, 'relationship_uuid');

            $table->uuid('place_uuid')->nullable()->change();
            $table->uuid('relationship_uuid')->nullable()->change();

            $table->foreign('place_uuid')
                ->references('uuid')
                ->on('place');

            $table->foreign('relationship_uuid')
                ->references('uuid')
                ->on('relationship');
        });
    }

    public function down(): void
    {
        // The 'down' of this patch is tricky. To ensure that the reverse of the 000000
        // patch works in all cases, that one must actually be resilient, and it already
        // uses dropIfExist. So we assume that not reversing the above fixes, will not
        // be a problem, since wer'e not doing rollback of separate steps anyway.
    }

    private function constraintExists(string $table, string $constraintName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $keysFound = $sm->listTableForeignKeys($table);

        foreach ($keysFound as $foreignKey) {
            if ($constraintName === $foreignKey->getName()) {
                return true;
            }
        }

        return false;
    }

    private function dropForeignIfExists(Blueprint $table, string $fkColumn): void
    {
        $tableName = $table->getTable();
        $constraintName = $tableName . '_' . $fkColumn . '_foreign';

        if ($this->constraintExists($tableName, $constraintName)) {
            $table->dropForeign($constraintName);
        }
    }
}
