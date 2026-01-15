<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMoreWardsAndVillagesForTanzania extends Migration
{
    public function up()
    {
        $lgaTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        // Arusha - Arusha City Wards
        $arushaCity = DB::table($lgaTable)->where('name', 'Arusha City')->first();
        if ($arushaCity) {
            $wards = ['Baraa', 'Daraja II', 'Kaloleni', 'Lemara', 'Olorien', 'Sombetini', 'Themi'];
            foreach ($wards as $wardName) {
                $wardId = DB::table('wards')->insertGetId([
                    'lga_id' => $arushaCity->id,
                    'name' => $wardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                if ($wardName === 'Themi') {
                    DB::table('villages')->insert([
                        ['ward_id' => $wardId, 'name' => 'Themi Kati', 'created_at' => now(), 'updated_at' => now()],
                        ['ward_id' => $wardId, 'name' => 'Themi Kusini', 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }
        }

        // Mwanza - Ilemela Wards
        $ilemela = DB::table($lgaTable)->where('name', 'Ilemela')->first();
        if ($ilemela) {
            $wards = ['Buswelu', 'Ilemela', 'Kirumba', 'Kitangiri', 'Nyamanoro', 'Pasiansi'];
            foreach ($wards as $wardName) {
                $wardId = DB::table('wards')->insertGetId([
                    'lga_id' => $ilemela->id,
                    'name' => $wardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                if ($wardName === 'Kirumba') {
                    DB::table('villages')->insert([
                        ['ward_id' => $wardId, 'name' => 'Kirumba Kati', 'created_at' => now(), 'updated_at' => now()],
                        ['ward_id' => $wardId, 'name' => 'Kirumba Mashariki', 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }
        }

        // Dodoma - Dodoma City Wards
        $dodomaCity = DB::table($lgaTable)->where('name', 'Dodoma City')->first();
        if ($dodomaCity) {
            $wards = ['Dodoma Makulu', 'Hazina', 'Ipagala', 'Kizota', 'Majengo', 'Tambukareli'];
            foreach ($wards as $wardName) {
                $wardId = DB::table('wards')->insertGetId([
                    'lga_id' => $dodomaCity->id,
                    'name' => $wardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
    }
}
