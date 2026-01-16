<?php

namespace App\Repositories;

use App\Models\Nationality;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use App\Models\Village;
use App\Models\Place;

class LocationRepo
{
    public function getStates()
    {
        return State::all();
    }

    public function getAllStates()
    {
        return State::orderBy('name', 'asc')->get();
    }

    public function getStatesByCountry(?string $countryCode)
    {
        if ($countryCode) {
            $states = State::where('country_code', $countryCode)
                ->orderBy('name', 'asc')
                ->get();

            // If there are no states for this country code yet, fall back to all
            if ($states->isNotEmpty()) {
                return $states;
            }
        }

        // Fallback to all states if no country code mapping is available
        return $this->getAllStates();
    }

    public function getAllNationals()
    {
        return Nationality::orderBy('name', 'asc')->get();
    }

    public function getLGAs($state_id)
    {
        return Lga::where('state_id', $state_id)->orderBy('name', 'asc')->get();
    }

    public function getWards($lga_id)
    {
        return Ward::where('lga_id', $lga_id)->orderBy('name', 'asc')->get();
    }

    public function getVillages($ward_id)
    {
        return Village::where('ward_id', $ward_id)->orderBy('name', 'asc')->get();
    }

    public function getPlaces($village_id)
    {
        return Place::where('village_id', $village_id)->orderBy('name', 'asc')->get();
    }

    /**
     * Map a nationality name (e.g. "Tanzanian") to a country code (e.g. "TZ").
     * Existing Nigerian data is marked as "NG"; East African examples are added
     * here so that selecting those nationalities can filter states correctly.
     */
    public function mapNationalityToCountryCode(?string $nationality): ?string
    {
        if (! $nationality) {
            return null;
        }

        $normalized = trim($nationality);

        if ($normalized === '') {
            return null;
        }

        // Normalize to lowercase for comparison
        if (function_exists('mb_strtolower')) {
            $key = mb_strtolower($normalized, 'UTF-8');
        } else {
            $key = strtolower($normalized);
        }

        $map = [
            // Nigeria
            'nigerian' => 'NG',
            'nigeria'  => 'NG',

            // Tanzania
            'tanzanian' => 'TZ',
            'tanzania'  => 'TZ',

            // Kenya
            'kenyan' => 'KE',
            'kenya'  => 'KE',

            // Uganda
            'ugandan' => 'UG',
            'uganda'  => 'UG',

            // Rwanda
            'rwandan' => 'RW',
            'rwanda'  => 'RW',

            // Burundi
            'burundian' => 'BI',
            'burundi'   => 'BI',

            // Sudan (and South Sudan if you later add data)
            'sudanese'    => 'SD',
            'sudan'       => 'SD',
            'south sudan' => 'SS',

            // Ethiopia
            'ethiopian' => 'ET',
            'ethiopia'  => 'ET',

            // Somalia
            'somali'  => 'SO',
            'somalia' => 'SO',
        ];

        return $map[$key] ?? null;
    }

    public function findOrCreateWard($lga_id, $name)
    {
        if (!$name || !$lga_id) return null;
        return Ward::firstOrCreate([
            'lga_id' => $lga_id,
            'name' => ucwords($name)
        ]);
    }

    public function findOrCreateVillage($ward_id, $name)
    {
        if (!$name || !$ward_id) return null;
        return Village::firstOrCreate([
            'ward_id' => $ward_id,
            'name' => ucwords($name)
        ]);
    }

}
