<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('call-to-action')]
class CallToActionControllerTest extends ControllerTestCase
{
    public function testCreate(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get(sprintf('/editcase/%s/tasks/new', $case->uuid));
        $response->assertStatus(200);
        $response->assertViewIs('create-call-to-action');
        $response->assertViewHas('case.uuid', $case->uuid);

        $this->assertAuditEventForCase($case);
        $this->assertAuditObjectForOrganisation($case->organisation);
    }

    public function testIndex(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->get('/taken');
        $response->assertStatus(200);
        $response->assertViewIs('call-to-action');
    }
}
