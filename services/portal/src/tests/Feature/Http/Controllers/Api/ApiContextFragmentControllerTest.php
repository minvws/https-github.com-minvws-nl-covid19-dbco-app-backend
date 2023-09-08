<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Tests\Feature\FeatureTestCase;

use function sprintf;

class ApiContextFragmentControllerTest extends FeatureTestCase
{
    public function testGetFragments(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->get(sprintf('/api/contexts/%s/fragments', $context->uuid));
        $response->assertOk();
    }

    public function testGetFragment(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->get(sprintf('/api/contexts/%s/fragments/%s', $context->uuid, 'general'));
        $response->assertOk();
    }
}
