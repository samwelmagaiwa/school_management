<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PopulateTanzaniaLocationsDetails extends Migration
{
    public function up()
    {
        // This migration seeds richer TZ location data; skip entirely if
        // the base states table (and country codes) are not yet present.
        if (! Schema::hasTable('states')) {
            return;
        }

        $lgaTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        // Get Tanzania States (Regions)
        $regions = DB::table('states')->where('country_code', 'TZ')->get();
        $regionMap = $regions->pluck('id', 'name');

        $data = [
            'Arusha' => ['Arusha City', 'Arusha Rural', 'Meru', 'Karatu', 'Monduli', 'Ngorongoro', 'Longido'],
            'Dar es Salaam' => ['Ilala', 'Kinondoni', 'Temeke', 'Kigamboni', 'Ubungo'],
            'Dodoma' => ['Dodoma City', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'],
            'Geita' => ['Bukombe', 'Chato', 'Geita', 'Mbogwe', 'Nyang\'hwale'],
            'Iringa' => ['Iringa Rural', 'Iringa Municipal', 'Kilolo', 'Mufindi', 'Mafinga'],
            'Kagera' => ['Biharamulo', 'Bukoba Rural', 'Bukoba Municipal', 'Kyerwa', 'Missenyi', 'Muleba', 'Ngara', 'Karagwe'],
            'Katavi' => ['Mlele', 'Mpimbwe', 'Mpanda Town'],
            'Kigoma' => ['Buhigwe', 'Kakonko', 'Kasulu Rural', 'Kasulu Town', 'Kigoma Rural', 'Kigoma Urban', 'Uvinza'],
            'Kilimanjaro' => ['Hai', 'Moshi Rural', 'Moshi Municipal', 'Mwanga', 'Rombo', 'Same', 'Siha'],
            'Lindi' => ['Kilwa', 'Lindi Rural', 'Lindi Municipal', 'Nachingwea', 'Ruangwa', 'Liwale'],
            'Manyara' => ['Babati Rural', 'Babati Town', 'Hanang', 'Kiteto', 'Mbulu Rural', 'Mbulu Town', 'Simanjiro'],
            'Mara' => ['Bunda', 'Butiama', 'Musoma Rural', 'Musoma Municipal', 'Rorya', 'Serengeti', 'Tarime'],
            'Mbeya' => ['Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Mbeya Rural', 'Mbeya City', 'Rungwe'],
            'Morogoro' => ['Gairo', 'Ifakara', 'Kilombero', 'Kilosa', 'Morogoro Rural', 'Morogoro Municipal', 'Mvomero', 'Ulanga', 'Malinyi'],
            'Mtwara' => ['Masasi Rural', 'Masasi Town', 'Mtwara Rural', 'Mtwara Municipal', 'Nanyumbu', 'Newala', 'Tandahimba'],
            'Mwanza' => ['Ilemela', 'Kwimba', 'Magu', 'Misungwi', 'Nyamagana', 'Sengerema', 'Ukerewe'],
            'Njombe' => ['Ludewa', 'Makambako', 'Njombe Rural', 'Njombe Town', 'Wanging\'ombe'],
            'Pwani' => ['Bagamoyo', 'Kibaha Rural', 'Kibaha Town', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji'],
            'Rukwa' => ['Kalambo', 'Nkasi', 'Sumbawanga Rural', 'Sumbawanga Municipal'],
            'Ruvuma' => ['Mbinga Rural', 'Mbinga Town', 'Songea Rural', 'Songea Municipal', 'Tunduru', 'Namtumbo', 'Nyasa'],
            'Shinyanga' => ['Kahama Rural', 'Kahama Town', 'Kishapu', 'Shinyanga Rural', 'Shinyanga Municipal'],
            'Simiyu' => ['Bariadi', 'Busega', 'Itilima', 'Maswa', 'Meatu'],
            'Singida' => ['Iramba', 'Ikungi', 'Manyoni', 'Mkalama', 'Singida Rural', 'Singida Municipal'],
            'Songwe' => ['Ileje', 'Mbozi', 'Momba', 'Songwe'],
            'Tabora' => ['Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Tabora Municipal', 'Urambo', 'Uyui'],
            'Tanga' => ['Handeni Rural', 'Handeni Town', 'Kilindi', 'Korogwe Rural', 'Korogwe Town', 'Lushoto', 'Muheza', 'Pangani', 'Tanga City'],
        ];

        foreach ($data as $regionName => $districts) {
            if (isset($regionMap[$regionName])) {
                $regionId = $regionMap[$regionName];
                foreach ($districts as $districtName) {
                    // Check if exists to avoid duplicates (Dar es Salaam districts were added partially)
                    $exists = DB::table($lgaTable)->where('name', $districtName)->where('state_id', $regionId)->exists();
                    if (!$exists) {
                        DB::table($lgaTable)->insert([
                            'name' => $districtName,
                            'state_id' => $regionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Add Wards for Kinondoni (Example)
        $kinondoni = DB::table($lgaTable)->where('name', 'Kinondoni')->first();
        if ($kinondoni) {
            $wards = ['Bunju', 'Hananasif', 'Kawe', 'Kigogo', 'Kijitonyama', 'Kinondoni', 'Kunduchi', 'Mabwepande', 'Magomeni', 'Makongo', 'Makumbusho', 'Mbezi Juu', 'Mbweni', 'Mikocheni', 'Msasani', 'Mwananyamala', 'Mzimuni', 'Ndugumbi', 'Tandale', 'Wazo'];
            foreach ($wards as $wardName) {
                $wardId = DB::table('wards')->insertGetId([
                    'lga_id' => $kinondoni->id,
                    'name' => $wardName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add sample village for Kunduchi
                if ($wardName === 'Kunduchi') {
                    $villages = ['Ununio', 'Mtongani', 'Tegeta', 'Kunduchi Pwani'];
                    foreach ($villages as $villageName) {
                        DB::table('villages')->insert([
                            'ward_id' => $wardId,
                            'name' => $villageName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        // No easy way to down without affecting other data, but we can delete TZ specific ones if needed.
    }
}
