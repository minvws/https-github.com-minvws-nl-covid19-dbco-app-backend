<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function array_values;

class OrganisationSeeder extends Seeder
{
    // WARNING: the code below assumes department ids *never* match organisation ids, if this changes, please fix the code!

    // organisation used by the development team on acceptance / staging, users of this organisation don't have production roles
    public const GHOR = '00000';

    // external organisations registered as organisations
    private const SOS_CED = '99004';
    private const TELEPERFORMANCE = '99005';
    private const WEBHELP = '99007';

    // external organisations registered as department
    private const YOURCE = 'yource';
    private const MAJOREL = 'majorel';
    private const RIFF = 'riff';

    public function run(): void
    {
        // Table based on https://docs.google.com/spreadsheets/d/1cMivbTHma-EjQUkF-NjID4jbdSMyYUN44LHPsi2zqD8/edit#gid=0
        $rows = [
            [
                'external_id' => self::GHOR,
                'name' => 'GGD GHOR',
                'type' => OrganisationType::regionalGGD(),
                'abbreviation' => 'GHOR',
            ],
            [
                'external_id' => self::SOS_CED,
                'name' => 'SOS/CED',
                'type' => OrganisationType::outsourceOrganisation(),
            ],
            [
                'external_id' => self::YOURCE,
                'name' => 'Yource',
                'type' => OrganisationType::outsourceDepartment(),
            ],
            [
                'external_id' => self::MAJOREL,
                'name' => 'Majorel',
                'type' => OrganisationType::outsourceDepartment(),
            ],
            [
                'external_id' => self::TELEPERFORMANCE,
                'name' => 'Teleperformance',
                'type' => OrganisationType::outsourceOrganisation(),
            ],
            [
                'external_id' => self::WEBHELP,
                'name' => 'Webhelp',
                'type' => OrganisationType::outsourceOrganisation(),
            ],
            [
                'external_id' => self::RIFF,
                'name' => 'Riff',
                'type' => OrganisationType::outsourceDepartment(),
            ],
            [
                'external_id' => '01003',
                'name' => 'GGD Groningen',
                'hp_zone_code' => '646410',
                'phone_number' => '050 - 367 40 00',
                'abbreviation' => 'GLO',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '02003',
                'name' => 'GGD Fryslân',
                'hp_zone_code' => '646406',
                'phone_number' => '088 - 229 93 33',
                'abbreviation' => 'FRY',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '03003',
                'name' => 'GGD Drenthe',
                'hp_zone_code' => '646403',
                'phone_number' => '0592 - 709 709',
                'abbreviation' => 'DRE',
                'outsources_to' => [self::WEBHELP],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '04003',
                'name' => 'GGD IJsselland',
                'hp_zone_code' => '646415',
                'phone_number' => '088 - 443 03 70',
                'abbreviation' => 'IJS',
                'outsources_to' => [self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '05003',
                'name' => 'GGD Twente',
                'hp_zone_code' => '646422',
                'phone_number' => '053 - 487 68 40',
                'abbreviation' => 'TWE',
                'outsources_to' => [self::SOS_CED, self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '06003',
                'name' => 'GGD Noord- en Oost Gelderland',
                'hp_zone_code' => '646417',
                'phone_number' => '088 - 4433 777',
                'abbreviation' => 'NOG',
                'outsources_to' => [self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '07003',
                'name' => 'GGD Gelderland-Midden',
                'hp_zone_code' => '646407',
                'phone_number' => '0800 - 844 6000',
                'abbreviation' => 'GLM',
                'outsources_to' => [self::WEBHELP, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '08003',
                'name' => 'GGD Gelderland-Zuid',
                'hp_zone_code' => '646408',
                'phone_number' => '088 - 1447 123',
                'abbreviation' => 'GLZ',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '09003',
                'name' => 'GGD Regio Utrecht',
                'hp_zone_code' => '646419',
                'phone_number' => '030 - 209 93 03',
                'abbreviation' => 'UTR',
                'outsources_to' => [],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '10003',
                'name' => 'GGD Hollands Noorden',
                'hp_zone_code' => '646414',
                'phone_number' => '088 - 0100 533',
                'abbreviation' => 'HNO',
                'outsources_to' => [self::SOS_CED, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '11003',
                'name' => 'GGD Zaanstreek Waterland',
                'hp_zone_code' => '646424',
                'phone_number' => '075 - 651 8388',
                'abbreviation' => 'ZWD',
                'outsources_to' => [self::WEBHELP, self::SOS_CED],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '12003',
                'name' => 'GGD Kennemerland',
                'hp_zone_code' => '646416',
                'phone_number' => '023 - 789 16 31',
                'abbreviation' => 'KEN',
                'outsources_to' => [self::SOS_CED, self::WEBHELP],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '13003',
                'name' => 'GGD Amsterdam',
                'hp_zone_code' => '646401',
                'phone_number' => '020 - 55 55 570',
                'abbreviation' => 'AMS',
                'outsources_to' => [self::SOS_CED, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '14003',
                'name' => 'GGD Gooi- en Vechtstreek',
                'hp_zone_code' => '646409',
                'phone_number' => '035 - 692 64 00',
                'abbreviation' => 'GEV',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '15003',
                'name' => 'GGD Haaglanden',
                'hp_zone_code' => '646411',
                'phone_number' => '088 - 355 01 00',
                'abbreviation' => 'HAG',
                'outsources_to' => [self::MAJOREL],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '16003',
                'name' => 'GGD Hollands Midden',
                'hp_zone_code' => '646413',
                'phone_number' => '085 - 078 28 78',
                'abbreviation' => 'HMI',
                'outsources_to' => [self::RIFF, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '17003',
                'name' => 'GGD Rotterdam Rijnmond',
                'hp_zone_code' => '646420',
                'phone_number' => '010 - 433 9270',
                'abbreviation' => 'RR',
                'outsources_to' => [self::RIFF, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '18003',
                'name' => 'GGD Zuid Holland Zuid',
                'hp_zone_code' => '646426',
                'phone_number' => '078 - 770 85 80',
                'abbreviation' => 'ZLZ',
                'outsources_to' => [self::WEBHELP, self::SOS_CED],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '19003',
                'name' => 'GGD Zeeland',
                'hp_zone_code' => '646425',
                'phone_number' => '0113 - 249 442',
                'abbreviation' => 'ZEE',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '20003',
                'name' => 'GGD West Brabant',
                'hp_zone_code' => '646423',
                'phone_number' => '085 - 078 5810',
                'abbreviation' => 'WBT',
                'outsources_to' => [self::SOS_CED, self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '21003',
                'name' => 'GGD Hart voor Brabant',
                'hp_zone_code' => '646412',
                'phone_number' => '088 - 368 7777',
                'abbreviation' => 'HVB',
                'outsources_to' => [self::MAJOREL],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '22003',
                'name' => 'GGD Brabant Zuidoost',
                'hp_zone_code' => '646402',
                'phone_number' => '088 - 003 15 95',
                'abbreviation' => 'BZO',
                'outsources_to' => [self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '23003',
                'name' => 'GGD Limburg-Noord',
                'hp_zone_code' => '646418',
                'phone_number' => '088 - 119 19 40',
                'abbreviation' => 'NLG',
                'outsources_to' => [self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '24003',
                'name' => 'GGD Zuid Limburg',
                'hp_zone_code' => '646427',
                'phone_number' => '088 - 880 50 05',
                'abbreviation' => 'ZLG',
                'outsources_to' => [self::WEBHELP, self::RIFF],
                'type' => OrganisationType::regionalGGD(),
            ],
            [
                'external_id' => '25003',
                'name' => 'GGD Flevoland',
                'hp_zone_code' => '646405',
                'phone_number' => '085 - 200 7710',
                'abbreviation' => 'FLV',
                'outsources_to' => [self::YOURCE],
                'type' => OrganisationType::regionalGGD(),
            ],
        ];

        foreach ($rows as &$row) {
            $row['hp_zone_code'] ??= null;
            $row['phone_number'] ??= null;
            $row['abbreviation'] ??= null;
            $row['outsources_to'] ??= [];
            $row['has_outsource_toggle'] = 0;
        }

        $this->upsert($rows);
    }

    /**
     * Because the oracle driver doesn't support upsert, we have to build our own here.
     */
    private function upsert(array $values): void
    {
        $now = CarbonImmutable::now();

        $externalIdToUuid = [];

        foreach ($values as $row) {
            unset($row['outsources_to']);

            $existing = EloquentOrganisation::where('external_id', $row['external_id'])->first();

            if ($existing) {
                $externalIdToUuid[$row['external_id']] = $existing->uuid;

                // needs update?
                if (
                    $existing->name !== $row['name']
                    || $existing->phone_number !== $row['phone_number']
                    || $existing->abbreviation !== $row['abbreviation']
                    || $existing->type !== $row['type']->value
                ) {
                    // Update needed
                    $row['updated_at'] = $now;
                    DB::table('organisation')->where('external_id', $row['external_id'])->update($row);
                }
            } else {
                $row['uuid'] = (string) Str::uuid();
                $externalIdToUuid[$row['external_id']] = $row['uuid'];
                $row['created_at'] = $now;
                DB::table('organisation')->insert($row);
            }
        }

        DB::table("organisation_outsource")->delete();
        foreach ($values as $row) {
            $organisationUuid = $externalIdToUuid[$row['external_id']];
            foreach ($row['outsources_to'] as $outsourceExternalId) {
                $outsourcesToOrganisationUuid = $externalIdToUuid[$outsourceExternalId];
                DB::table('organisation_outsource')->insert([
                    'organisation_uuid' => $organisationUuid,
                    'outsources_to_organisation_uuid' => $outsourcesToOrganisationUuid,
                ]);
            }
        }

        foreach (array_values($externalIdToUuid) as $uuid) {
            $exists = DB::table('case_list')
                ->where('organisation_uuid', $uuid)
                ->where('is_default', 1)
                ->where('is_queue', 1)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('case_list')->insert([
                'uuid' => (string) Str::uuid(),
                'organisation_uuid' => $uuid,
                'name' => 'Wachtrij',
                'is_default' => 1,
                'is_queue' => 1,
            ]);
        }
    }
}
