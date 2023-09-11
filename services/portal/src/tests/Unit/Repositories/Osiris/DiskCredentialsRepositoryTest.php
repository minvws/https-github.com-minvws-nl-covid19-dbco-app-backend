<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Osiris;

use App\Exceptions\Osiris\CouldNotRetrieveCredentials;
use App\Models\Versions\Organisation\OrganisationCommon;
use App\Repositories\Osiris\DiskCredentialsRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Mockery\MockInterface;
use Tests\Unit\UnitTestCase;

use function json_encode;
use function sprintf;

final class DiskCredentialsRepositoryTest extends UnitTestCase
{
    protected Filesystem&MockInterface $filesystem;
    protected OrganisationCommon&MockInterface $organisation;

    protected function setUp(): void
    {
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->organisation = Mockery::mock(OrganisationCommon::class);
    }

    public function testItThrowsAnExceptionWhenCredentialsFileIsEmpty(): void
    {
        $credentialsPath = $this->faker->filePath();

        $this->filesystem
            ->shouldReceive('get')
            ->with($credentialsPath)
            ->andReturn('');

        $this->expectException(CouldNotRetrieveCredentials::class);

        $credentialsRepository = new DiskCredentialsRepository($credentialsPath, $this->filesystem);
        $credentialsRepository->getForOrganisation($this->organisation);
    }

    public function testItThrowsAnExceptionWhenCredentialsFileDoesNotExist(): void
    {
        $credentialsPath = $this->faker->word();

        $this->filesystem->shouldReceive('get')
            ->with($credentialsPath)
            ->andThrow(FileNotFoundException::class);


        $this->expectException(CouldNotRetrieveCredentials::class);

        $credentialsRepository = new DiskCredentialsRepository($credentialsPath, $this->filesystem);
        $credentialsRepository->getForOrganisation($this->organisation);
    }

    public function testItThrowsAnExceptionWhenCredentialsFileContainsInvalidJson(): void
    {
        $credentialsPath = $this->faker->filePath();
        $organisationCredentials = '{';

        $this->filesystem
            ->shouldReceive('get')
            ->with($credentialsPath)
            ->andReturn($organisationCredentials);

        $this->expectException(CouldNotRetrieveCredentials::class);

        $credentialsRepository = new DiskCredentialsRepository($credentialsPath, $this->filesystem);
        $credentialsRepository->getForOrganisation($this->organisation);
    }

    public function testItThrowsExceptionWhenCredentialsNotFoundForOrganisation(): void
    {
        $credentialsPath = $this->faker->filePath();
        $externalId = $this->faker->bothify('######');
        $organisationCredentials = '{}';
        $organisation = new class implements OrganisationCommon {
            public string $externalId;
        };
        $organisation->externalId = $externalId;

        $this->filesystem
            ->shouldReceive('get')
            ->with($credentialsPath)
            ->andReturn($organisationCredentials);

        $this->expectException(CouldNotRetrieveCredentials::class);
        $this->expectExceptionMessage(sprintf('No match for organisation with external ID %s', $externalId));

        $credentialsRepository = new DiskCredentialsRepository($credentialsPath, $this->filesystem);
        $credentialsRepository->getForOrganisation($organisation);
    }

    public function testItReturnsOrganisationCredentialsWhenUuidIsInCredentialsFile(): void
    {
        $credentialsPath = $this->faker->filePath();
        $sysLogin = $this->faker->userName;
        $sysPassword = $this->faker->password;
        $userLogin = $this->faker->userName;
        $externalId = $this->faker->bothify('######');
        $organisationCredentials = json_encode([
            $externalId => [
                'sysLogin' => $sysLogin,
                'sysPassword' => $sysPassword,
                'osirisGebruikerLogin' => $userLogin,
            ],
        ]);
        $organisation = new class implements OrganisationCommon {
            public string $externalId;
        };
        $organisation->externalId = $externalId;

        $this->filesystem
            ->shouldReceive('get')
            ->with($credentialsPath)
            ->andReturn($organisationCredentials);

        $credentialsRepository = new DiskCredentialsRepository($credentialsPath, $this->filesystem);
        $credentials = $credentialsRepository->getForOrganisation($organisation);

        $this->assertEquals($sysLogin, $credentials->sysLogin);
        $this->assertEquals($sysPassword, $credentials->sysPassword);
        $this->assertEquals($userLogin, $credentials->userLogin);
    }
}
