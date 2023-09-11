<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\EloquentUser;
use DateTimeInterface;
use Illuminate\Support\Collection;

interface AssignmentRepository
{
    public function createAssignment(string $choreId, string $userUuid, ?DateTimeInterface $expiresAt): string;

    public function deleteAssignment(string $assignmentId): void;

    public function cleanupExpiredAssignments(): void;

    public function findByChoreAndUser(string $choreId, string $userUuid): ?Assignment;

    public function findCaseUuidsByAssignmentToUser(EloquentUser $user): array;

    public function listChoreAssignments(string $choreId, int $limit): Collection;
}
