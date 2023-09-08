<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Export\ExportClientPurpose;
use App\Models\Purpose\Purpose;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function json_decode;
use function property_exists;
use function random_bytes;
use function sodium_crypto_box_seed_keypair;

use const SODIUM_CRYPTO_BOX_NONCEBYTES;
use const SODIUM_CRYPTO_BOX_SEEDBYTES;

#[Group('export')]
#[Group('export-client')]
class ExportClientTest extends FeatureTestCase
{
    public function testCreateClientEncryptsPseudoIdColumns(): void
    {
        $pseudoIdKeyPair = sodium_crypto_box_seed_keypair(random_bytes(SODIUM_CRYPTO_BOX_SEEDBYTES));
        $pseudoIdNonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

        $client = $this->createExportClient([
            'name' => 'Name',
            'pseudo_id_key_pair' => $pseudoIdKeyPair,
            'pseudo_id_nonce' => $pseudoIdNonce,
        ]);
        $row = DB::table('export_client')->where('id', '=', $client->id)->first();

        $this->assertIsObject($row);
        $this->assertNotEmpty($row->pseudo_id_key_pair);
        $this->assertNotEmpty($row->pseudo_id_nonce);
        $this->assertNotEquals($pseudoIdKeyPair, $row->pseudo_id_key_pair);
        $this->assertNotEquals($pseudoIdNonce, $row->pseudo_id_nonce);

        $decodedKeyPair = json_decode($row->pseudo_id_key_pair);
        $this->assertIsObject($decodedKeyPair);
        $this->assertTrue(property_exists($decodedKeyPair, 'ciphertext'), 'Object property "ciphertext" is missing.');
        $this->assertTrue(property_exists($decodedKeyPair, 'nonce'), 'Object property "nonce" is missing.');
        $this->assertEquals('export_client', $decodedKeyPair->key);

        $decodedNonce = json_decode($row->pseudo_id_nonce);
        $this->assertIsObject($decodedKeyPair);
        $this->assertTrue(property_exists($decodedNonce, 'ciphertext'), 'Object property "ciphertext" is missing.');
        $this->assertTrue(property_exists($decodedNonce, 'nonce'), 'Object property "nonce" is missing.');
        $this->assertEquals('export_client', $decodedNonce->key);
    }

    public function testCreateClientPurposeWithEpidemiologicalSurveillance(): void
    {
        $client = $this->createExportClient();

        /** @var ExportClientPurpose $exportClientPurpose */
        $exportClientPurpose = $client->purposes()->make();
        $exportClientPurpose->purpose = Purpose::EpidemiologicalSurveillance;
        $exportClientPurpose->save();

        $client->refresh();
        $this->assertDatabaseHas('export_client_purpose', [
            'export_client_id' => $client->id,
            'purpose' => Purpose::EpidemiologicalSurveillance->value,
        ]);
        $this->assertTrue($client->getPurposeLimitation()->hasPurpose(Purpose::EpidemiologicalSurveillance));
        $this->assertTrue(!$client->getPurposeLimitation()->hasPurpose(Purpose::QualityOfCare));
    }

    public function testCreateClientPurposeWithEpidemiologicalSurveillanceAndQualityOfCare(): void
    {
        $client = $this->createExportClient();

        /** @var ExportClientPurpose $exportClientPurpose1 */
        $exportClientPurpose1 = $client->purposes()->make();
        $exportClientPurpose1->purpose = Purpose::EpidemiologicalSurveillance;
        $exportClientPurpose1->save();

        /** @var ExportClientPurpose $exportClientPurpose2 */
        $exportClientPurpose2 = $client->purposes()->make();
        $exportClientPurpose2->purpose = Purpose::QualityOfCare;
        $exportClientPurpose2->save();

        $client->refresh();
        $this->assertDatabaseHas('export_client_purpose', [
            'export_client_id' => $client->id,
            'purpose' => Purpose::QualityOfCare->value,
        ]);
        $this->assertTrue($client->getPurposeLimitation()->hasPurpose(Purpose::EpidemiologicalSurveillance));
        $this->assertTrue($client->getPurposeLimitation()->hasPurpose(Purpose::QualityOfCare));
    }

    public function testCreateClientOrganisations(): void
    {
        $client = $this->createExportClient();
        $orgA = $this->createOrganisation();
        $orgB = $this->createOrganisation();

        $this->assertDatabaseMissing('export_client_organisation', [
            'export_client_id' => $client->id,
            'organisation_uuid' => $orgA->uuid,
        ]);

        $client->organisations()->save($orgA);

        $this->assertDatabaseHas('export_client_organisation', [
            'export_client_id' => $client->id,
            'organisation_uuid' => $orgA->uuid,
        ]);
        $this->assertDatabaseMissing('export_client_organisation', [
            'export_client_id' => $client->id,
            'organisation_uuid' => $orgB->uuid,
        ]);
    }

    public function testAuthenticatable(): void
    {
        $client = $this->createExportClient();

        $this->assertEquals('id', $client->getAuthIdentifierName());
        $this->assertEquals($client->id, $client->getAuthIdentifier());
    }

    public function testAuthenticatableGetAuthPasswordUnsupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createExportClient()->getAuthPassword();
    }

    public function testAuthenticatableGetRememberTokenUnsupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createExportClient()->getRememberToken();
    }

    public function testAuthenticatableSetRememberTokenUnsupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createExportClient()->setRememberToken('');
    }

    public function testAuthenticatableGetRememberTokenNameUnsupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createExportClient()->getRememberTokenName();
    }
}
