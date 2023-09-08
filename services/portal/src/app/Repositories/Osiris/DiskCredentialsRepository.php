<?php

declare(strict_types=1);

namespace App\Repositories\Osiris;

use App\Dto\Osiris\Client\Credentials;
use App\Exceptions\Osiris\CouldNotRetrieveCredentials;
use App\Models\Versions\Organisation\OrganisationCommon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use JsonException;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use Webmozart\Assert\Assert;

use function json_decode;

use const JSON_THROW_ON_ERROR;

final class DiskCredentialsRepository implements CredentialsRepository
{
    public function __construct(
        #[Config('services.osiris.api_login_path')]
        private readonly string $credentialsPath,
        private readonly Filesystem $file,
    ) {
    }

    public function getForOrganisation(OrganisationCommon $organisation): Credentials
    {
        try {
            $credentialsJson = $this->file->get($this->credentialsPath);
            $credentials = json_decode($credentialsJson, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (FileNotFoundException | JsonException $exception) {
            throw new CouldNotRetrieveCredentials(previous: $exception);
        }

        Assert::isArray($credentials, 'Credentials file must return an array');

        $organisationCredentials = $credentials[$organisation->externalId] ?? null;
        if ($organisationCredentials === null) {
            throw CouldNotRetrieveCredentials::becauseNoMatchForOrganisation($organisation->externalId);
        }

        Assert::isArray($organisationCredentials);
        Assert::string($organisationCredentials['sysLogin']);
        Assert::string($organisationCredentials['sysPassword']);
        Assert::string($organisationCredentials['osirisGebruikerLogin']);

        return new Credentials(
            $organisationCredentials['sysLogin'],
            $organisationCredentials['sysPassword'],
            $organisationCredentials['osirisGebruikerLogin'],
        );
    }
}
