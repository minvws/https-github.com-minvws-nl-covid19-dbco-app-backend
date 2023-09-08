<?php

declare(strict_types=1);

use App\Services\FragmentMigrationService;
use Illuminate\Database\Migrations\Migration;

class ChangeTaskJobWorksInAviationType extends Migration
{
    public function upFragment(stdClass $jobFragment): void
    {
        if (!property_exists($jobFragment, 'worksInAviation')) {
            return;
        }

        if ($jobFragment->worksInAviation === true) {
            $jobFragment->worksInAviation = 'yes';
        } elseif ($jobFragment->worksInAviation === false) {
            $jobFragment->worksInAviation = 'no';
        } else {
            $jobFragment->worksInAviation = null;
        }
    }

    public function up(): void
    {
        $fragmentMigrationService = app()->get(FragmentMigrationService::class);
        $fragmentMigrationService->task(['job'])->update(function (object $task, array &$fragments): void {
            if (!array_key_exists('job', $fragments)) {
                return;
            }

            $this->upFragment($fragments['job']);
        });
    }

    public function downFragment(stdClass $jobFragment): void
    {
        if (!property_exists($jobFragment, 'worksInAviation')) {
            return;
        }

        if ($jobFragment->worksInAviation === 'yes') {
            $jobFragment->worksInAviation = true;
        } elseif ($jobFragment->worksInAviation === 'no') {
            $jobFragment->worksInAviation = false;
        } else {
            $jobFragment->worksInAviation = null;
        }
    }

    public function down(): void
    {
        $fragmentMigrationService = app()->get(FragmentMigrationService::class);
        $fragmentMigrationService->task(['job'])->update(function (object $task, array &$fragments): void {
            if (!array_key_exists('job', $fragments)) {
                return;
            }

            $this->downFragment($fragments['job']);
        });
    }
}
