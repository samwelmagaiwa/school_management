<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportTanzaniaLocations extends Command
{
    protected $signature = 'import:tz-locations';
    protected $description = 'Import Tanzanian regions, districts, wards, and streets from GitHub (Optimized)';

    protected $stateCache = [];
    protected $districtCache = [];
    protected $wardCache = [];

    public function handle()
    {
        $baseUrl = 'https://raw.githubusercontent.com/HackEAC/tanzania-locations-db/main/location-files/';
        $files = [
            'arusha.csv', 'dar-es-salaam.csv', 'dodoma.csv', 'geita.csv', 'iringa.csv', 'kagera.csv',
            'katavi.csv', 'kigoma.csv', 'kilimanjaro.csv', 'lindi.csv', 'manyara.csv', 'mara.csv',
            'mbeya.csv', 'morogoro.csv', 'mtwara.csv', 'mwanza.csv', 'njombe.csv', 'pwani.csv',
            'rukwa.csv', 'ruvuma.csv', 'shinyanga.csv', 'simiyu.csv', 'singida.csv', 'songwe.csv',
            'tabora.csv', 'tanga.csv'
        ];

        $this->info('Starting optimized import of Tanzanian locations...');

        // Pre-load State Cache
        $this->stateCache = DB::table('states')->where('country_code', 'TZ')->pluck('id', 'name')->toArray();

        foreach ($files as $file) {
            $this->info("Processing $file...");
            $response = Http::get($baseUrl . $file);

            if ($response->failed()) {
                $this->error("Failed to download $file");
                continue;
            }

            $csvData = $response->body();
            $temp = tmpfile();
            fwrite($temp, $csvData);
            fseek($temp, 0);

            $header = array_map('strtolower', fgetcsv($temp) ?: []);
            if (!$header) {
                fclose($temp);
                continue;
            }

            $regionIdx = $this->findHeaderIndex($header, ['region']);
            $districtIdx = $this->findHeaderIndex($header, ['district']);
            $wardIdx = $this->findHeaderIndex($header, ['ward']);
            $streetIdx = $this->findHeaderIndex($header, ['street', 'village']);

            if ($regionIdx === false || $districtIdx === false || $wardIdx === false) {
                $this->error("Invalid CSV format in $file");
                fclose($temp);
                continue;
            }

            $batch = [];
            $chunkSize = 100;
            $count = 0;

            while (($row = fgetcsv($temp)) !== false) {
                if (count($row) < 3) continue;

                $regionName = $this->normalize($row[$regionIdx] ?? '');
                $districtName = $this->normalize($row[$districtIdx] ?? '');
                $wardName = $this->normalize($row[$wardIdx] ?? '');
                $streetName = isset($row[$streetIdx]) ? $this->normalize($row[$streetIdx]) : null;

                if (!$regionName || !$districtName || !$wardName) continue;

                // 1. State
                if (!isset($this->stateCache[$regionName])) {
                    $this->stateCache[$regionName] = DB::table('states')->insertGetId([
                        'name' => $regionName, 'country_code' => 'TZ', 'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                $stateId = $this->stateCache[$regionName];

                // 2. District
                $dKey = $stateId . '_' . $districtName;
                if (!isset($this->districtCache[$dKey])) {
                    $dist = DB::table('districts')->where('name', $districtName)->where('state_id', $stateId)->first();
                    $this->districtCache[$dKey] = $dist ? $dist->id : DB::table('districts')->insertGetId([
                        'name' => $districtName, 'state_id' => $stateId, 'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                $districtId = $this->districtCache[$dKey];

                // 3. Ward
                $wKey = $districtId . '_' . $wardName;
                if (!isset($this->wardCache[$wKey])) {
                    $ward = DB::table('wards')->where('name', $wardName)->where('lga_id' , $districtId)->first();
                    $this->wardCache[$wKey] = $ward ? $ward->id : DB::table('wards')->insertGetId([
                        'name' => $wardName, 'lga_id' => $districtId, 'created_at' => now(), 'updated_at' => now()
                    ]);
                }
                $wardId = $this->wardCache[$wKey];

                // 4. Village (Batching)
                if ($streetName && !in_array(strtolower($streetName), ['none', '', 'null'])) {
                    $batch[] = [
                        'name' => $streetName,
                        'ward_id' => $wardId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (count($batch) >= $chunkSize) {
                    DB::table('villages')->insertOrIgnore($batch);
                    $batch = [];
                }
            }
            if (count($batch) > 0) {
                DB::table('villages')->insertOrIgnore($batch);
            }

            fclose($temp);
        }

        $this->info('Tanzanian locations imported successfully!');
    }

    private function normalize($name) {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords(strtolower($name));
        $name = str_replace(' Es ', ' es ', $name);
        return trim(str_replace(["\r", "\n"], ' ', $name));
    }

    private function findHeaderIndex($header, $patterns)
    {
        foreach ($header as $idx => $name) {
            foreach ($patterns as $pattern) {
                if (str_contains($name, $pattern)) {
                    return $idx;
                }
            }
        }
        return false;
    }
}
