<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @deprecated Job is moved to \App\Jobs\ContactSearchHashJob. This should be removed after queue is depleted of
 * this (old) job. Also add "final" to moved job class after removal of this temp job!
 */
final class EloquentCaseSearchHashJob extends ContactSearchHashJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
}
