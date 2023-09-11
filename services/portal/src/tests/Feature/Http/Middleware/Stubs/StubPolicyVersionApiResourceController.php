<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware\Stubs;

use App\Http\Controllers\Controller;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;

final class StubPolicyVersionApiResourceController extends Controller
{
    public function index(PolicyVersion $policyVersion): array
    {
        return ['action' => 'index'];
    }

    public function show(PolicyVersion $policyVersion, CalendarItem $calendarItem): array
    {
        return ['action' => 'show'];
    }

    public function destroy(PolicyVersion $policyVersion, CalendarItem $calendarItem): array
    {
        return ['action' => 'destroy'];
    }

    public function store(PolicyVersion $policyVersion): array
    {
        return ['action' => 'store'];
    }

    public function update(PolicyVersion $policyVersion, CalendarItem $calendarItem): array
    {
        return ['action' => 'update'];
    }
}
