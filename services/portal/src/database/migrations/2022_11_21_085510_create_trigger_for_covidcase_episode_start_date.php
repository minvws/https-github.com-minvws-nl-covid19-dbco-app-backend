<?php

declare(strict_types=1);

use App\Models\Enums\Db\TriggerActionTime;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            $this->getCreateTriggerStatement(TriggerActionTime::BEFORE_INSERT),
        );
        DB::statement(
            $this->getCreateTriggerStatement(TriggerActionTime::BEFORE_UPDATE),
        );
    }

    public function down(): void
    {
        DB::statement(
            $this->getDropTriggerStatement($this->makeTriggerName(TriggerActionTime::BEFORE_INSERT)),
        );
        DB::statement(
            $this->getDropTriggerStatement($this->makeTriggerName(TriggerActionTime::BEFORE_UPDATE)),
        );
    }

    private function getCreateTriggerStatement(TriggerActionTime $actionTime): string
    {
        return sprintf(
            'CREATE TRIGGER `%s` %s ON covidcase FOR EACH ROW SET NEW.episode_start_date = COALESCE(NEW.date_of_symptom_onset, NEW.date_of_test, NEW.created_at);',
            $this->makeTriggerName($actionTime),
            $actionTime->statement(),
        );
    }

    private function getDropTriggerStatement(string $name): string
    {
        return "DROP TRIGGER IF EXISTS `$name`";
    }

    private function makeTriggerName(TriggerActionTime $actionTime): string
    {
        return sprintf(
            'trg_covidcase_%s_esd',
            $actionTime->alias(),
        );
    }
};
