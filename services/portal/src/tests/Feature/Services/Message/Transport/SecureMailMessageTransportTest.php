<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message\Transport;

use App\Exceptions\AttachmentException;
use App\Exceptions\IdentifiedBsnNotValidAnymoreException;
use App\Exceptions\MessageException;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\Attachment;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Task\PersonalDetails;
use App\Models\Task\TaskAddress;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\LocalBsnRepository;
use App\Services\Bsn\BsnService;
use App\Services\Message\AttachmentFileHelper;
use App\Services\Message\AttachmentService;
use App\Services\Message\Transport\SecureMailMessageTransport;
use App\Services\SecureMail\SecureMailClient;
use App\Services\SecureMail\SecureMailMessage;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;
use MinVWS\DBCO\Enum\Models\Gender;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function base64_encode;

#[Group('secure-mail-send-message')]
class SecureMailMessageTransportTest extends FeatureTestCase
{
    public function testSendWithCase(): void
    {
        $address = IndexAddress::newInstanceWithVersion(1);
        $address->houseNumber = (string) $this->faker->randomNumber();
        $address->postalCode = $this->faker->postcode;

        $index = Index::newInstanceWithVersion(1);
        $index->dateOfBirth = CarbonImmutable::instance($this->faker->dateTime)->floorDay();
        $index->address = $address;

        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'index' => $index,
        ]);

        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'pseudo_bsn' => null,
        ]);

        /** @var BsnService|MockInterface $bsnService */
        $bsnService = $this->mock(
            BsnService::class,
            static function (MockInterface $mock): void {
                $mock->expects('newExchangeTokenForIdentifiedPseudoBsn')->never();
            },
        );

        $mailerIdentifier = $this->faker->uuid();
        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->createSecureMailClientMock($eloquentMessage, $mailerIdentifier);

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'bsnService' => $bsnService,
            'secureMailClient' => $secureMailClient,
        ]);
        $returnedMailerIdentifier = $smtpMailerMessageTransport->send($eloquentMessage);

        $this->assertEquals($mailerIdentifier, $returnedMailerIdentifier);
    }

    public function testSendWithTask(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $task = $this->createTaskForCase($case, [
            'personal_details' => PersonalDetails::newInstanceWithVersion(
                1,
                function (PersonalDetails $personalDetails): void {
                    $personalDetails->dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);
                    $personalDetails->address->houseNumber = (string) $this->faker->randomNumber();
                    $personalDetails->address->postalCode = $this->faker->postcode;
                },
            ),
        ]);

        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'pseudo_bsn' => $this->faker->numerify,
        ]);

        /** @var BsnService|MockInterface $bsnService */
        $bsnService = $this->mock(
            BsnService::class,
            function (MockInterface $mock): void {
                $mock->expects('newExchangeTokenForIdentifiedPseudoBsn')
                    ->andReturn($this->faker->uuid);
            },
        );

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(
            SecureMailClient::class,
            static function (MockInterface $mock) use ($eloquentMessage): void {
                $mock->expects('postMessage')
                    ->with(Mockery::on(static function (SecureMailMessage $message) use ($eloquentMessage): bool {
                        return $message->aliasId === $eloquentMessage->task_uuid
                            && $message->toEmail === $eloquentMessage->to_email
                            && $message->toName === $eloquentMessage->to_name
                            && $message->subject === $eloquentMessage->subject;
                    }))
                    ->once();
            },
        );

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'bsnService' => $bsnService,
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendFromAddress(): void
    {
        $fromEmail = $this->faker->safeEmail;
        $fromName = $this->faker->company();

        $address = IndexAddress::newInstanceWithVersion(1);
        $address->houseNumber = (string) $this->faker->randomNumber();
        $address->postalCode = $this->faker->postcode;

        $index = Index::newInstanceWithVersion(1);
        $index->dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);
        $index->address = $address;

        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'index' => $index,
        ]);

        $eloquentMessage = $this->createMessage([
            'pseudo_bsn' => $this->faker->numerify,
            'case_uuid' => $case->uuid,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
        ]);

        /** @var BsnService|MockInterface $bsnService */
        $bsnService = $this->mock(
            BsnService::class,
            function (MockInterface $mock): void {
                $mock->expects('newExchangeTokenForIdentifiedPseudoBsn')
                    ->andReturn($this->faker->uuid);
            },
        );

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(
            SecureMailClient::class,
            static function (MockInterface $mock) use ($fromName, $fromEmail): void {
                $mock->expects('postMessage')
                    ->with(Mockery::on(
                        static function (SecureMailMessage $message) use (
                            $fromEmail,
                            $fromName,
                        ): bool {
                            return $message->fromName === $fromName
                                && $message->fromEmail === $fromEmail;
                        },
                    ))
                    ->once();
            },
        );

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'bsnService' => $bsnService,
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendWithPseudoBsn(): void
    {
        $pseudoBsn = $this->faker->uuid();
        $exchangeToken = $this->faker->uuid();

        $address = IndexAddress::newInstanceWithVersion(1);
        $address->houseNumber = (string) $this->faker->randomNumber();
        $address->postalCode = $this->faker->postcode;

        $index = Index::newInstanceWithVersion(1);
        $index->dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);
        $index->address = $address;

        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'index' => $index,
        ]);

        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'pseudo_bsn' => $pseudoBsn,
        ]);

        /** @var BsnService|MockInterface $bsnService */
        $bsnService = $this->mock(
            BsnService::class,
            static function (MockInterface $mock) use ($exchangeToken): void {
                $mock->expects('newExchangeTokenForIdentifiedPseudoBsn')
                    ->andReturn($exchangeToken);
            },
        );

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(
            SecureMailClient::class,
            static function (MockInterface $mock) use ($exchangeToken): void {
                $mock->expects('postMessage')
                    ->with(Mockery::on(static function (SecureMailMessage $message) use ($exchangeToken): bool {
                        return $message->pseudoBsnToken === $exchangeToken;
                    }))
                    ->once();
            },
        );

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'bsnService' => $bsnService,
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendToIndexWithIdentifiedBsn(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'pseudo_bsn_guid' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990007,
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->firstname = $this->faker->firstName();
                $index->lastname = $this->faker->lastName();
                $index->gender = Gender::male();
                $index->dateOfBirth = DateTimeImmutable::createFromFormat(
                    'Ymd',
                    LocalBsnRepository::DATE_OF_BIRTH_FIXTURE_999990007,
                );

                $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                    $address->postalCode = LocalBsnRepository::POSTAL_CODE_FIXTURE_999990007;
                    $address->houseNumber = LocalBsnRepository::HOUSE_NUMBER_FIXTURE_999990007;
                });
            }),
        ]);
        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'identity_required' => true,
            'pseudo_bsn' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990007,
        ]);

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->createSecureMailClientMock($eloquentMessage);

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendToContactWithIdentifiedBsn(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $task = $this->createTaskForCase($case, [
            'pseudo_bsn_guid' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990007,
            'personal_details' => static function () {
                /** @var PersonalDetails $personalDetails */
                $personalDetails = PersonalDetails::getSchema()->getCurrentVersion()->newInstance();
                $personalDetails->dateOfBirth = DateTimeImmutable::createFromFormat(
                    'Ymd',
                    LocalBsnRepository::DATE_OF_BIRTH_FIXTURE_999990007,
                );
                /** @var TaskAddress $address */
                $address = TaskAddress::getSchema()->getCurrentVersion()->newInstance();
                $address->postalCode = LocalBsnRepository::POSTAL_CODE_FIXTURE_999990007;
                $address->houseNumber = LocalBsnRepository::HOUSE_NUMBER_FIXTURE_999990007;
                $personalDetails->address = $address;
                return $personalDetails;
            },
        ]);
        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'identity_required' => true,
            'pseudo_bsn' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990007,
        ]);

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->createSecureMailClientMock($eloquentMessage);

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendRequiredIdentityMessageWithMissingCredentials(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'identity_required' => true,
            'pseudo_bsn' => $this->faker->uuid(),
        ]);

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('lookupPseudoBsn')
                ->andReturn([]);
        });

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(SecureMailClient::class);

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'secureMailClient' => $secureMailClient,
        ]);

        $this->expectException(MessageException::class);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendToIndexWithIdentifiedBsnWhichIsNotValidAnymore(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'pseudo_bsn_guid' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990019, //Differs from index details
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->firstname = $this->faker->firstName();
                $index->lastname = $this->faker->lastName();
                $index->gender = Gender::male();
                $index->dateOfBirth = DateTimeImmutable::createFromFormat(
                    'Ymd',
                    LocalBsnRepository::DATE_OF_BIRTH_FIXTURE_999990007,
                );

                $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address): void {
                    $address->postalCode = LocalBsnRepository::POSTAL_CODE_FIXTURE_999990007;
                    $address->houseNumber = LocalBsnRepository::HOUSE_NUMBER_FIXTURE_999990007;
                });
            }),
        ]);
        $eloquentMessage = $this->createMessage([
            'case_uuid' => $case->uuid,
            'identity_required' => true,
            'pseudo_bsn' => LocalBsnRepository::PSEUDO_BSN_FIXTURE_999990019,
        ]);

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(SecureMailClient::class);

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'secureMailClient' => $secureMailClient,
        ]);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage(IdentifiedBsnNotValidAnymoreException::EXCEPTION_MESSAGE);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }

    public function testSendWithAttachments(): void
    {
        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween();

                $index->address = IndexAddress::newInstanceWithVersion(1, function (IndexAddress $address): void {
                    $address->postalCode = $this->faker->postcode();
                    $address->houseNumber = (string) $this->faker->randomNumber(2);
                });
            }),
        ]);
        $message = $this->createMessageForCase($case, [
            'pseudo_bsn' => null,
        ]);

        /** @var Attachment $attachment */
        $attachment = Attachment::query()->firstOrFail();
        $message->attachments()->attach($attachment);

        /** @var SecureMailClient|MockInterface $secureMailClient */
        $secureMailClient = $this->mock(
            SecureMailClient::class,
            static function (MockInterface $mock) use ($attachment): void {
                $mock->expects('postMessage')
                    ->with(
                        Mockery::on(static function (SecureMailMessage $secureMailMessage) use ($attachment): bool {
                            $filesystem = Storage::disk(AttachmentFileHelper::FILESYSTEM_DISK_ATTACHMENTS);
                            $content = $filesystem->get($attachment->file_name);
                            $mimeType = $filesystem->mimeType($attachment->file_name);

                            return $secureMailMessage->attachments === [
                                [
                                    'filename' => $attachment->file_name,
                                    'content' => base64_encode($content),
                                    'mime_type' => $mimeType,
                                ],
                            ];
                        }),
                    );
            },
        );

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'secureMailClient' => $secureMailClient,
        ]);
        $smtpMailerMessageTransport->send($message);
    }

    public function testSendWithAttachmentsThrowsException(): void
    {
        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween();

                $index->address = IndexAddress::newInstanceWithVersion(1, function (IndexAddress $address): void {
                    $address->postalCode = $this->faker->postcode();
                    $address->houseNumber = (string) $this->faker->randomNumber(2);
                });
            }),
        ]);
        $message = $this->createMessageForCase($case, [
            'pseudo_bsn' => null,
        ]);

        /** @var Attachment $attachment */
        $attachment = Attachment::query()->firstOrFail();
        $message->attachments()->attach($attachment);

        $exceptionMessage = $this->faker->sentence();

        /** @var AttachmentService $attachmentService */
        $attachmentService = $this->mock(
            AttachmentService::class,
            static function (MockInterface $mock) use ($exceptionMessage): void {
                $mock->expects('convertToArray')
                    ->andThrow(new AttachmentException($exceptionMessage));
            },
        );

        $smtpMailerMessageTransport = $this->app->make(SecureMailMessageTransport::class, [
            'attachmentService' => $attachmentService,
        ]);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $smtpMailerMessageTransport->send($message);
    }

    private function createSecureMailClientMock(
        EloquentMessage $eloquentMessage,
        ?string $mailerIdentifier = null,
    ): MockInterface {
        if ($mailerIdentifier === null) {
            $mailerIdentifier = $this->faker->uuid();
        }

        return $this->mock(
            SecureMailClient::class,
            static function (MockInterface $mock) use ($eloquentMessage, $mailerIdentifier): void {
                $mock->expects('postMessage')
                    ->with(Mockery::on(static function (SecureMailMessage $message) use ($eloquentMessage): bool {
                        return $message->toEmail === $eloquentMessage->to_email
                            && $message->toName === $eloquentMessage->to_name
                            && $message->subject === $eloquentMessage->subject;
                    }))
                    ->andReturn($mailerIdentifier);
            },
        );
    }
}
