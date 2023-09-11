<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Schema\Fragment;
use App\Scopes\CaseAuthScope;
use App\Services\CaseFragmentService;
use Exception;
use MinVWS\Codable\JSONDecoder;
use Throwable;

use function preg_replace_callback;
use function strtolower;

class DbCaseFragmentRepository implements CaseFragmentRepository
{
    /**
     * Converts a fragment name to its column name.
     */
    private function columnNameForFragmentName(string $fragmentName): string
    {
        /**
         * alternateContact => alternate_contact
         *
         * @var string $columnName
         */
        $columnName = preg_replace_callback('/[A-Z]/', static fn($m) => '_' . strtolower($m[0]), $fragmentName);

        return $columnName;
    }

    /**
     * @inheritDoc
     */
    public function loadCaseFragments(string $caseUuid, array $fragmentNames, bool $includingSoftDeletes = false, bool $disableAuthFilter = false): array
    {
        $case = EloquentCase::query()
            ->when($includingSoftDeletes, static function ($query): void {
                $query->withTrashed();
            })
            ->when($disableAuthFilter, static function ($query): void {
                $query->withoutGlobalScope(CaseAuthScope::class);
            })
            ->find($caseUuid);

        if ($case === null) {
            throw new Exception("Case not found");
        }

        $fragmentClasses = CaseFragmentService::fragmentClasses();
        $decoder = new JSONDecoder();

        $result = [];
        foreach ($fragmentNames as $fragmentName) {
            $fragmentClass = $fragmentClasses[$fragmentName];
            $result[$fragmentName] = $this->loadCaseFragment($case, $fragmentName, $fragmentClass, $decoder);
        }

        return $result;
    }

    /**
     * Retrieve case fragment from case instance.
     *
     * @throws Exception
     */
    private function loadCaseFragment(EloquentCase $case, string $fragmentName, string $fragmentClass, JSONDecoder $decoder): object
    {
        try {
            return $case->$fragmentName ?? $this->createCaseFragment($case, $fragmentClass);
        } catch (Throwable $e) {
            // should not happen, so make it a runtime exception so that if it occurs we
            // return a 501 and the error gets logged
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function storeCaseFragments(string $caseUuid, array $fragments): void
    {
        $case = null;
        foreach ($fragments as $fragment) {
            if ($fragment instanceof Fragment && $fragment->getOwner() !== null) {
                $case = $fragment->getOwner();
                break;
            }
        }

        if (!$case instanceof EloquentCase) {
            $case = EloquentCase::find($caseUuid);
        }

        if (!$case instanceof EloquentCase) {
            throw new Exception("Case not found");
        }

        foreach ($fragments as $fragmentName => $fragment) {
            $case->$fragmentName = $fragment;
        }

        if (!$case->save()) {
            throw new Exception("Unable to store case fragments");
        }
    }

    /**
     * Create new case fragment.
     *
     * @template T
     */
    private function createCaseFragment(EloquentCase $case, string $fragmentClass): object
    {
        return new $fragmentClass();
    }
}
