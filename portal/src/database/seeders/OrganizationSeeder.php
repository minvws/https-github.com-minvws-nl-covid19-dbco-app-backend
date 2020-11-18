<?php

namespace Database\Seeders;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the organization seed.
     *
     * @return void
     */
    public function run()
    {
        // Table received from TIH
        $orgs = [
            '00000' => 'GGD GHOR Nederland',
            '01003' => 'GGD Groningen',
            '02003' => 'GGD Fryslân',
            '03003' => 'GGD Drenthe',
            '03101' => 'ICare JGZ',
            '04003' => 'GGD IJsselland',
            '05003' => 'GGD Twente',
            '06003' => 'GGD Noord Oost Gelderland',
            '06101' => 'Yunio',
            '06102' => 'ICare JGZ',
            '06103' => 'CJG Apeldoorn',
            '07003' => 'GGD Gelderland Midden',
            '08003' => 'Gelderland-Zuid',
            '09003' => 'GGD Regio Utrecht HelloID',
            '09101' => 'Gemeente Utrecht',
            '10003' => 'GGD Hollands Noorden',
            '11003' => 'GGD Zaanstreek - Waterland',
            '12003' => 'GGD Kennemerland',
            '13003' => 'GGD Amsterdam',
            '14003' => 'GGD Gooi en Vechtstreek',
            '15003' => 'GGD Haaglanden O365',
            '15102' => 'JGZ ZuidHollandWest',
            '16003' => 'RDOG HM',
            '17003' => 'GGD Rotterdam',
            '17101' => 'CJG Rijnmond',
            '18003' => 'Dienst Gezondheid en Jeugd ZHZ',
            '19003' => 'GGD Zeeland',
            '20003' => 'GGD West Brabant',
            '21003' => 'GGD Hart voor Brabant',
            '22003' => 'GGD Brabant-ZuidOost',
            '23003' => 'GGD Limburg Noord',
            '24003' => 'GGD Zuid Limburg',
            '25003' => 'GGD Flevoland',
            '25102' => 'ICare JGZ',
            '25105' => 'Zorggroep Oude en Nieuweland'
        ];

        $rows = [];
        foreach($orgs as $externalId => $name) {
            $rows[] = ['external_id' => $externalId, 'name' => $name];
        }

        $this->upsert($rows);
    }

    /**
     * Because the oracle driver doesn't suppport upsert, we have to build our own here.
     */
    private function upsert(array $values)
    {
        $now = Date::now();

        foreach($values as $row) {
            $existing = DB::table('organisation')->where('external_id', $row['external_id'])->get()->first();
            if ($existing) {
                // needs update?
                if ($existing->name != $row['name']) {
                    // Update needed
                    $row['updated_at'] = $now;
                    DB::table('organisation')->where('external_id', $row['external_id'])->update($row);
                }
            } else {
                $row['uuid'] = (string)Str::uuid();
                $row['created_at'] = $now;
                DB::table('organisation')->insert($row);
            }
        }
    }
}
