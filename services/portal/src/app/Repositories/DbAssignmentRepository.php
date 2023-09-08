<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

use function array_map;

class DbAssignmentRepository implements AssignmentRepository
{
    use LimitChecker;

    public function __construct(private readonly LoggerInterface $log)
    {
    }

    public function createAssignment(string $choreId, string $userUuid, ?DateTimeInterface $expiresAt): string
    {
        $assignment = new Assignment();

        $assignment->user_uuid = $userUuid;
        $assignment->chore_uuid = $choreId;
        $assignment->expires_at = $expiresAt;

        $assignment->save();

        return $assignment->uuid;
    }

    public function deleteAssignment(string $assignmentId): void
    {
        $assignment = Assignment::findOrFail($assignmentId);

        $assignment->delete();
    }

    public function cleanupExpiredAssignments(): void
    {
        Assignment::where('expires_at', '<', CarbonImmutable::now())
            ->withoutGlobalScope('active')
            ->update(['deleted_at' => CarbonImmutable::now()]);
    }

    public function findByChoreAndUser(string $choreId, string $userUuid): ?Assignment
    {
        return Assignment::where('chore_uuid', $choreId)
            ->where('user_uuid', $userUuid)
            ->first();
    }

    public function findCaseUuidsByAssignmentToUser(EloquentUser $user): array
    {
        // Note:
        // Inlining the below line causes a PHP/Apache Segmentation Fault when triggered through an external api call:
        $versions = array_map(
            static fn (int $version): string => "covid-case-v{$version}",
            EloquentCase::getSchema()->getVersions(),
        );

        $result = Assignment::query()
            ->leftJoin('chore', static function (JoinClause $leftJoin) use ($versions): void {
                $leftJoin
                    ->on('assignment.chore_uuid', 'chore.uuid')
                    ->whereIn('chore.resource_type', $versions);
            })
            ->where('assignment.user_uuid', $user->uuid)
            ->limit($this->hardLimit)
            ->pluck('chore.resource_id')
            ->toArray();

        $this->limitChecker($this->log, $result);

        return $result;
    }

    public function listChoreAssignments(string $choreId, int $limit = 0): Collection
    {
        $query = Assignment::where('chore_uuid', $choreId)
            ->orderBy('created_at', 'DESC')
            ->withoutGlobalScope('active')
            ->withTrashed();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
