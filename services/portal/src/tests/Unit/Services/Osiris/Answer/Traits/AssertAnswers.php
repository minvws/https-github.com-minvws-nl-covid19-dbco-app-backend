<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer\Traits;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\Builder;
use ReflectionClass;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder as BuilderAttribute;
use Tests\Unit\Services\Osiris\Answer\Test\TestAnswers;

use function app;
use function assert;
use function count;
use function is_a;
use function str_ends_with;
use function substr;

trait AssertAnswers
{
    protected function getBuilder(): Builder
    {
        $ref = new ReflectionClass(static::class);

        $attrs = $ref->getAttributes(BuilderAttribute::class);
        if (count($attrs) > 0) {
            $builderClass = $attrs[0]->newInstance()->class;
        } else {
            assert(str_ends_with($ref->getShortName(), 'BuilderTest'));
            $builderClass = 'App\\Services\\Osiris\\Answer\\' . substr($ref->getShortName(), 0, -4);
        }

        assert(is_a($builderClass, Builder::class, true));
        return app($builderClass);
    }

    /**
     * Asserts that the given answers list equals the expected answers list.
     *
     * @param array<Answer> $expectedAnswers
     * @param array<Answer> $answers
     */
    public function assertAnswers(array $expectedAnswers, array $answers, string $message = ''): void
    {
        (new TestAnswers($answers))->assertAnswers($expectedAnswers, $message);
    }

    /**
     * Asserts that the answers list contains all the expected answers.
     *
     * @param array<Answer> $expectedAnswers
     * @param array<Answer> $answers
     */
    public function assertContainsAnswers(array $expectedAnswers, array $answers, string $message = ''): void
    {
        (new TestAnswers($answers))->assertContainsAnswers($expectedAnswers, $message);
    }

    /**
     * Asserts that the given answers list *only* contains the given answer.
     *
     * @param array<Answer> $answers
     */
    public function assertAnswer(Answer $expectedAnswer, array $answers, string $message = ''): void
    {
        (new TestAnswers($answers))->assertAnswer($expectedAnswer, $message);
    }

    /**
     * Asserts that the given answers list contains the given answer.
     *
     * @param array<Answer> $answers
     */
    public function assertContainsAnswer(Answer $expectedAnswer, array $answers, string $message = ''): void
    {
        (new TestAnswers($answers))->assertContainsAnswer($expectedAnswer, $message);
    }

    /**
     * Builds the answers for the given case and returns an object that can be used to create assertions on the
     * answers returned by the builder.
     */
    public function answersForCase(EloquentCase $case): TestAnswers
    {
        $answers = $this->getBuilder()->build($case);
        return new TestAnswers($answers);
    }
}
