<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Note;
use App\Scopes\CaseAuthScope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\ServiceProvider;

use function array_merge;
use function is_null;

class EloquentServiceProvider extends ServiceProvider
{
    private static function getAllCaseVersions(): array
    {
        $morphMap = [];
        foreach (EloquentCase::getSchema()->getVersions() as $version) {
            $morphMap["covid-case-v$version"] = "App\\Models\\Versions\\CovidCase\\CovidCaseV$version";
        }
        return $morphMap;
    }

    public function boot(): void
    {
        Relation::enforceMorphMap(array_merge([
            'note' => Note::class,
            'case-assignment-history' => CaseAssignmentHistory::class,
            'expert-question' => ExpertQuestion::class,
            'covid-case' => EloquentCase::class,
            'bco-user' => EloquentUser::class,
            'task' => EloquentTask::class,
            'call-to-action' => CallToAction::class,
            'organisation' => EloquentOrganisation::class,
        ], self::getAllCaseVersions()));

        $this->extendBuilders();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app
            ->when(CaseAuthScope::class)
            ->needs('$runningInConsole')
            ->give($this->app->runningInConsole(...));
    }

    private function extendBuilders(): void
    {
        /**
         * @param callable(Enumerable,int,?Cursor):bool $callback
         */
        $chunkUsingCursor = function (int $perPage, callable $callback, ?Cursor $cursor = null): bool {
            $page = 1;

            do {
                /** @var Builder|EloquentBuilder $clone */
                $clone = clone $this;

                $cursorPaginateResult = $clone->cursorPaginate($perPage, cursor: $cursor);
                $cursor = $cursorPaginateResult->nextCursor();

                if ($cursorPaginateResult->isEmpty()) {
                    break;
                }

                $data = $clone instanceof EloquentBuilder
                    ? EloquentCollection::make($cursorPaginateResult->items())
                    : Collection::make($cursorPaginateResult->items());
                unset($cursorPaginateResult);

                if ($callback($data, $page, $cursor) === false) {
                    unset($data);

                    return false;
                }

                unset($data);

                $page++;
            } while (!is_null($cursor));

            return true;
        };

        EloquentBuilder::macro('chunkUsingCursor', $chunkUsingCursor);
        Builder::macro('chunkUsingCursor', $chunkUsingCursor);
    }
}
