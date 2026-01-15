<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Nationality;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    protected $loc, $my_class;

    public function __construct(LocationRepo $loc, MyClassRepo $my_class)
    {
        $this->loc = $loc;
        $this->my_class = $my_class;
    }

    /**
     * Get states/regions for a given nationality ID.
     */
    public function get_states($nal_id)
    {
        $nat = Nationality::find($nal_id);
        $code = $this->loc->mapNationalityToCountryCode($nat ? $nat->name : null);
        $states = $this->loc->getStatesByCountry($code);

        return $states->map(function ($q) {
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    /**
     * Get districts/LGAs for a given state/region.
     */
    public function get_lga($state_id)
    {
        $lgas = $this->loc->getLGAs($state_id);
        return $lgas->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    /**
     * Get wards for a given district/LGA.
     */
    public function get_wards($lga_id)
    {
        $wards = $this->loc->getWards($lga_id);
        return $wards->map(function ($q) {
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    /**
     * Get villages/streets for a given ward.
     */
    public function get_villages($ward_id)
    {
        $villages = $this->loc->getVillages($ward_id);
        return $villages->map(function ($q) {
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_sections($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        return $sections = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
    }

    public function get_class_subjects($class_id)
    {
        $sections = $this->my_class->getClassSections($class_id);
        $subjects = $this->my_class->findSubjectByClass($class_id);

        if(Qs::userIsTeacher()){
            $subjects = $this->my_class->findSubjectByTeacher(Auth::user()->id)->where('my_class_id', $class_id);
        }

        $d['sections'] = $sections->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();
        $d['subjects'] = $subjects->map(function($q){
            return ['id' => $q->id, 'name' => $q->name];
        })->all();

        return $d;
    }

}
