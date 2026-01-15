<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\State;

class CleanupTanzaniaLocations extends Command
{
    protected $signature = 'cleanup:tz-locations';
    protected $description = 'Merge redundant Tanzanian regions and normalize names';

    public function handle()
    {
        $this->info('Starting normalization of Tanzanian regions...');

        $states = State::where('country_code', 'TZ')->get();
        foreach ($states as $s) {
            $norm = str_replace(['-', '_'], ' ', $s->name);
            $norm = ucwords(strtolower($norm));
            $norm = str_replace(' Es ', ' es ', $norm);
            $norm = trim($norm);

            if ($s->name !== $norm) {
                $existing = State::where('country_code', 'TZ')->where('name', $norm)->where('id', '!=', $s->id)->first();
                if ($existing) {
                    $this->info("Merging {$s->name} (ID: {$s->id}) into {$existing->name} (ID: {$existing->id})");
                    DB::table('districts')->where('state_id', $s->id)->update(['state_id' => $existing->id]);
                    $s->delete();
                } else {
                    $this->info("Renaming {$s->name} to {$norm}");
                    $s->update(['name' => $norm]);
                }
            }
        }

        // Handle exact name duplicates that normalize to the same thing
        $duplicates = DB::table('states')
            ->select('name', DB::raw('MIN(id) as primary_id'))
            ->where('country_code', 'TZ')
            ->groupBy('name')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $d) {
            $this->info("Handling exact name duplicate: {$d->name}");
            $others = State::where('country_code', 'TZ')->where('name', $d->name)->where('id', '!=', $d->primary_id)->get();
            foreach ($others as $o) {
                $this->info("Merging ID {$o->id} into {$d->primary_id}");
                DB::table('districts')->where('state_id', $o->id)->update(['state_id' => $d->primary_id]);
                $o->delete();
            }
        }

        // 2. Cleanup Districts
        $this->info('Cleaning up duplicate districts...');
        $dupDistricts = DB::table('districts')
            ->select('name', 'state_id', DB::raw('MIN(id) as primary_id'))
            ->groupBy('name', 'state_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        foreach ($dupDistricts as $dd) {
            $this->info("Merging duplicate district: {$dd->name} in state {$dd->state_id}");
            $others = DB::table('districts')->where('name', $dd->name)->where('state_id', $dd->state_id)->where('id', '!=', $dd->primary_id)->get();
            foreach ($others as $o) {
                DB::table('wards')->where('lga_id', $o->id)->update(['lga_id' => $dd->primary_id]);
                DB::table('districts')->where('id', $o->id)->delete();
            }
        }

        // 3. Cleanup Wards
        $this->info('Cleaning up duplicate wards...');
        $dupWards = DB::table('wards')
            ->select('name', 'lga_id', DB::raw('MIN(id) as primary_id'))
            ->groupBy('name', 'lga_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        foreach ($dupWards as $dw) {
            $this->info("Merging duplicate ward: {$dw->name} in district {$dw->lga_id}");
            $others = DB::table('wards')->where('name', $dw->name)->where('lga_id', $dw->lga_id)->where('id', '!=', $dw->primary_id)->get();
            foreach ($others as $o) {
                DB::table('villages')->where('ward_id', $o->id)->update(['ward_id' => $dw->primary_id]);
                DB::table('wards')->where('id', $o->id)->delete();
            }
        }

        // 4. Cleanup Villages
        $this->info('Cleaning up duplicate villages...');
        $dupVillages = DB::table('villages')
            ->select('name', 'ward_id', DB::raw('MIN(id) as primary_id'))
            ->groupBy('name', 'ward_id')
            ->having(DB::raw('count(*)'), '>', 1)
            ->get();

        foreach ($dupVillages as $dv) {
            $this->info("Merging duplicate village: {$dv->name} in ward {$dv->ward_id}");
            DB::table('villages')
                ->where('name', $dv->name)
                ->where('ward_id', $dv->ward_id)
                ->where('id', '!=', $dv->primary_id)
                ->delete();
        }

        $this->info('Cleanup complete!');
    }
}
