<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use Illuminate\Support\Collection;

trait AssignmentAllowedByToken
{
    private function allowedByModel(Token $token, AssignmentModelEnum $model, array $uuids): bool
    {
        $allowedUuids = $token->res
            ->groupBy(static fn (TokenResource $r): string => $r->mod->name)
            ->get($model->name, Collection::make())
            ->flatMap(static fn (TokenResource $res): array => $res->ids);

        return Collection::make($uuids)->every(static fn (string $uuids): bool => $allowedUuids->contains($uuids));
    }
}
