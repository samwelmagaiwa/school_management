<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTanzaniaRegionsAndDistricts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $regions = [
            'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera', 'Katavi', 'Kigoma', 
            'Kilimanjaro', 'Lindi', 'Manyara', 'Mara', 'Mbeya', 'Morogoro', 'Mtwara', 'Mwanza', 
            'Njombe', 'Pemba North', 'Pemba South', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga', 
            'Simiyu', 'Singida', 'Songwe', 'Tabora', 'Tanga', 'Zanzibar North', 'Zanzibar South', 'Zanzibar West'
        ];

        // The table was renamed from 'lgas' to 'districts' in a previous migration
        $lgaTable = Schema::hasTable('districts') ? 'districts' : 'lgas';

        foreach ($regions as $region) {
            $regionId = DB::table('states')->insertGetId([
                'name' => $region,
                'country_code' => 'TZ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add some sample districts for Dar es Salaam
            if ($region === 'Dar es Salaam') {
                $districts = ['Ilala', 'Kinondoni', 'Temeke', 'Kigamboni', 'Ubungo'];
                foreach ($districts as $district) {
                    DB::table($lgaTable)->insert([
                        'name' => $district,
                        'state_id' => $regionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $lgaTable = Schema::hasTable('districts') ? 'districts' : 'lgas';
        $regionIds = DB::table('states')->where('country_code', 'TZ')->pluck('id');
        DB::table($lgaTable)->whereIn('state_id', $regionIds)->delete();
        DB::table('states')->whereIn('id', $regionIds)->delete();
    }
}
