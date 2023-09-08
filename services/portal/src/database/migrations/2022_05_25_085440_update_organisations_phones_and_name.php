<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationsPhonesAndName extends Migration
{
    public function up(): void
    {
        DB::table('organisation')
            ->where('external_id', '03003')
            ->update(['phone_number' => '0592 - 709 709']);

        DB::table('organisation')
            ->where('external_id', '21003')
            ->update(['phone_number' => '088 - 368 7777']);

        DB::table('organisation')
            ->where('external_id', '19003')
            ->update(['phone_number' => '0113 - 249 442']);

        DB::table('organisation')
            ->where('external_id', '17003')
            ->update(['phone_number' => '010 - 433 9270']);

        DB::table('organisation')
            ->where('external_id', '20003')
            ->update(['phone_number' => '085 - 078 5810']);

        DB::table('organisation')
            ->where('external_id', '25003')
            ->update(['phone_number' => '085 - 200 7710']);

        DB::table('organisation')
            ->where('external_id', '08003')
            ->update(['phone_number' => '088 - 1447 123']);

        DB::table('organisation')
            ->where('external_id', '06003')
            ->update(['phone_number' => '088 - 4433 777']);

        DB::table('organisation')
            ->where('external_id', '24003')
            ->update(['name' => 'GGD Zuid Limburg']);
    }

    public function down(): void
    {
        DB::table('organisation')
            ->where('external_id', '03003')
            ->update(['phone_number' => '0592 - 30 63 00']);

        DB::table('organisation')
            ->where('external_id', '21003')
            ->update(['phone_number' => '0900 - 364 64 64']);

        DB::table('organisation')
            ->where('external_id', '19003')
            ->update(['phone_number' => '0113 - 24 94 00']);

        DB::table('organisation')
            ->where('external_id', '17003')
            ->update(['phone_number' => '010 - 443 80 31']);

        DB::table('organisation')
            ->where('external_id', '20003')
            ->update(['phone_number' => '088 - 368 68 58']);

        DB::table('organisation')
            ->where('external_id', '25003')
            ->update(['phone_number' => '088 - 002 99 10']);

        DB::table('organisation')
            ->where('external_id', '08003')
            ->update(['phone_number' => '088 - 144 72 72']);

        DB::table('organisation')
            ->where('external_id', '06003')
            ->update(['phone_number' => '088 - 443 33 55']);

        DB::table('organisation')
            ->where('external_id', '24003')
            ->update(['name' => 'GGD Zuid-Limburg']);
    }
}
