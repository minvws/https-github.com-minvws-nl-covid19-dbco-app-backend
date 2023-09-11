<?php

declare(strict_types=1);

namespace Tests\Unit\Migrations;

use ChangeTaskJobWorksInAviationType;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\TestCase;

use function base_path;
use function property_exists;

#[Group('migration')]
#[Group('migration-task-job')]
class ChangeTaskJobWorksInAviationTypeTest extends TestCase
{
    private ChangeTaskJobWorksInAviationType $migration;

    protected function setUp(): void
    {
        parent::setUp();

        include_once base_path('database/migrations/2021_10_01_104423_change_task_job_works_in_aviation_type.php');
        $this->migration = new ChangeTaskJobWorksInAviationType();
    }

    public function testUp(): void
    {
        $jobFragment = new stdClass();

        $this->migration->upFragment($jobFragment);
        $this->assertFalse(property_exists($jobFragment, 'worksInAviation'));

        $jobFragment->worksInAviation = null;
        $this->migration->upFragment($jobFragment);
        $this->assertEquals(null, $jobFragment->worksInAviation);

        $jobFragment->worksInAviation = true;
        $this->migration->upFragment($jobFragment);
        $this->assertEquals('yes', $jobFragment->worksInAviation);

        $jobFragment->worksInAviation = false;
        $this->migration->upFragment($jobFragment);
        $this->assertEquals('no', $jobFragment->worksInAviation);
    }

    public function testDown(): void
    {
        $jobFragment = new stdClass();

        $this->migration->downFragment($jobFragment);
        $this->assertFalse(property_exists($jobFragment, 'worksInAviation'));

        $jobFragment->worksInAviation = null;
        $this->migration->downFragment($jobFragment);
        $this->assertEquals(null, $jobFragment->worksInAviation);

        $jobFragment->worksInAviation = 'yes';
        $this->migration->downFragment($jobFragment);
        $this->assertEquals(true, $jobFragment->worksInAviation);

        $jobFragment->worksInAviation = 'no';
        $this->migration->downFragment($jobFragment);
        $this->assertEquals(false, $jobFragment->worksInAviation);

        $jobFragment->worksInAviation = 'unknown';
        $this->migration->downFragment($jobFragment);
        $this->assertEquals(null, $jobFragment->worksInAviation);
    }
}
