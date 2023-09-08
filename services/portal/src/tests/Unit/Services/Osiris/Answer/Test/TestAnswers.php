<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer\Test;

use App\Services\Osiris\Answer\Answer;
use ArrayAccess;
use Countable;
use Illuminate\Testing\Assert as PHPUnit;
use IteratorAggregate;
use RuntimeException;
use Traversable;

use function array_map;
use function count;
use function implode;
use function strcmp;
use function usort;

class TestAnswers implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<Answer> $answers
     */
    public function __construct(private readonly array $answers)
    {
    }

    /**
     * Asserts that the given answers list equals the expected answers list.
     *
     * @param array<Answer> $expectedAnswers
     */
    public function assertAnswers(array $expectedAnswers, string $message = ''): self
    {
        $message = $message ?: $this->answersToString($this->answers) . ' is not equal to ' . $this->answersToString($expectedAnswers);

        PHPUnit::assertEquals(count($expectedAnswers), count($this->answers), $message);

        $answers = $this->answers;
        $this->sortAnswers($expectedAnswers);
        $this->sortAnswers($answers);

        foreach ($expectedAnswers as $i => $expectedAnswer) {
            $this->assertAnswersEqual($expectedAnswer, $answers[$i], $message);
        }

        return $this;
    }

    /**
     * Asserts that the answers list contains all the expected answers.
     *
     * @param array<Answer> $expectedAnswers
     */
    public function assertContainsAnswers(array $expectedAnswers, string $message = ''): self
    {
        $allFound = true;

        foreach ($expectedAnswers as $expectedAnswer) {
            $found = false;

            foreach ($this->answers as $answer) {
                $found = $this->answersEqual($expectedAnswer, $answer);
                if ($found) {
                    break;
                }
            }

            $allFound = $found;
            if (!$allFound) {
                break;
            }
        }

        $message = $message ?: $this->answersToString($this->answers) . ' does not contain ' . $this->answersToString($expectedAnswers);
        PHPUnit::assertTrue($allFound, $message);

        return $this;
    }

    /**
     * Asserts that the answers list *only* contains the given answer.
     */
    public function assertAnswer(Answer $expectedAnswer, string $message = ''): self
    {
        $this->assertAnswers([$expectedAnswer], $message);
        return $this;
    }

    /**
     * Asserts that the answers list contains the given answer.
     */
    public function assertContainsAnswer(Answer $expectedAnswer, string $message = ''): self
    {
        $this->assertContainsAnswers([$expectedAnswer], $message);
        return $this;
    }

    public function assertEmpty(string $message = ''): self
    {
        PHPUnit::assertEmpty($this->answers, $message);
        return $this;
    }

    public function assertCount(int $count, string $message = ''): self
    {
        PHPUnit::assertCount($count, $this->answers, $message);
        return $this;
    }

    private function sortAnswers(array &$answers): void
    {
        usort($answers, static fn (Answer $a, Answer $b) => strcmp($a->code, $b->code));
    }

    private function answerToString(Answer $answer): string
    {
        return "Answer<code={$answer->code}, value={$answer->value}>";
    }

    private function answersToString(array $answers): string
    {
        return '[' . implode(', ', array_map(fn (Answer $a) => $this->answerToString($a), $answers)) . ']';
    }

    private function answersEqual(Answer $a, Answer $b): bool
    {
        return $a->code === $b->code && $a->value === $b->value;
    }

    private function assertAnswersEqual(Answer $expectedAnswer, Answer $answer, string $message = ''): void
    {
        $message = $message ?: $this->answerToString($answer) . ' is not equal to ' . $this->answerToString($expectedAnswer);
        PHPUnit::assertTrue($this->answersEqual($expectedAnswer, $answer), $message);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->answers[$offset]);
    }

    public function offsetGet(mixed $offset): Answer
    {
        return $this->answers[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Unsupported');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Unsupported');
    }

    public function count(): int
    {
        return count($this->answers);
    }

    /**
     * @return Traversable<int, Answer>
     */
    public function getIterator(): Traversable
    {
        yield from $this->answers;
    }
}
