<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Intake;

use App\Services\Intake\IncomingMessageToRawIntakeConverter;
use App\Services\MessageQueue\AMQPIncomingMessage;
use Carbon\CarbonImmutable;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Tests\DataProvider\RawIntakeDataProvider;

use function json_encode;

class IncomingMesageToRawIntakeConverterTest extends IntakeFeatureTestCase
{
    #[DataProviderExternal(RawIntakeDataProvider::class, 'validRawIntakeDataProvider')]
    public function testConvert(array $identityData, array $intakeData, ?array $handoverData): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $encryptedIdentityData = $this->encryptIdentityData($identityData);
        $encryptedIntakeData = $this->encryptIntakeData($intakeData);
        $encryptedHandoverData = $this->encryptHandoverData($handoverData);

        $body = json_encode([
            'id' => 'intake-id',
            'data' => [
                'type' => 'intake-type',
                'source' => 'intake-source',
                'identityData' => $encryptedIdentityData,
                'intakeData' => $encryptedIntakeData,
                'handoverData' => $encryptedHandoverData,
                'receivedAt' => CarbonImmutable::now()->toIso8601String(),
            ],
        ]);

        $message = new AMQPMessage($body);
        $incomingMessage = new AMQPIncomingMessage($message);

        $incomingMessageToRawIntakeConverter = $this->app->get(IncomingMessageToRawIntakeConverter::class);
        $rawIntake = $incomingMessageToRawIntakeConverter->convert($incomingMessage);

        $this->assertEquals('intake-id', $rawIntake->getId());
        $this->assertEquals('intake-type', $rawIntake->getType());
        $this->assertEquals('intake-source', $rawIntake->getSource());
        $this->assertEquals($identityData, $rawIntake->getIdentityData());
        $this->assertEquals($intakeData, $rawIntake->getIntakeData());
        $this->assertEquals($handoverData, $rawIntake->getHandoverData());
        $this->assertTrue(CarbonImmutable::now()->equalTo($rawIntake->getReceivedAt()));
    }
}
