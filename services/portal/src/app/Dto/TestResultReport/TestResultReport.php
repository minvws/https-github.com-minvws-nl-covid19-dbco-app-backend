<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final class TestResultReport
{
    public string $orderId;
    public string $messageId;
    public string $ggdIdentifier;
    public Test $test;
    public Person $person;
    public Triage $triage;
    public DateTimeInterface $receivedAt;
    public string $raw;

    public static function fromArray(array $array): self
    {
        $self = new self();

        $self->orderId = $array['orderId'];
        $self->messageId = $array['messageId'];
        $self->ggdIdentifier = $array['ggdIdentifier'];
        $self->test = Test::fromArray($array['test']);
        $self->person = Person::fromArray($array['person']);
        $self->triage = Triage::fromArray($array['triage']);

        try {
            $self->receivedAt = new DateTimeImmutable($array['receivedAt']);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to parse receivedAt', 0, $exception);
        }

        $self->raw = json_encode($array, JSON_THROW_ON_ERROR);

        return $self;
    }
}
