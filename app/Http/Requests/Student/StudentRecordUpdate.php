<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Qs;

class StudentRecordUpdate extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:6|max:150',
            'gender' => 'required|string',
            'phone' => 'sometimes|nullable|string|min:6|max:20',
            'email' => 'sometimes|nullable|email|max:100|unique:users,id',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:6|max:120',
            'bg_id' => 'sometimes|nullable',
            'my_class_id' => 'required',
            'section_id' => 'required',
            'state_id' => 'required',
            'lga_id' => 'required',
            'nal_id' => 'required',
            'my_parent_id' => 'sometimes|nullable',
            'dorm_id' => 'sometimes|nullable|exists:dorms,id',
            'dorm_room_id' => 'sometimes|nullable|exists:dorm_rooms,id',
            'dorm_bed_id' => 'sometimes|nullable|exists:dorm_beds,id',
            'ward' => 'required|string|min:2|max:120',
            'street' => 'required|string|min:2|max:120',
        ];
    }

    public function attributes()
    {
        return  [
            'nal_id' => 'Nationality',
            'dorm_id' => 'Dormitory',
            'dorm_room_id' => 'Dorm Room',
            'dorm_bed_id' => 'Dorm Bed',
            'state_id' => 'State',
            'lga_id' => 'LGA',
            'bg_id' => 'Blood Group',
            'my_parent_id' => 'Parent',
            'my_class_id' => 'Class',
            'section_id' => 'Section',
            'ward' => 'Ward',
            'street' => 'Street / Village',
        ];
    }

    protected function getValidatorInstance()
    {
        $input = $this->all();

        $input['my_parent_id'] = $input['my_parent_id'] ? Qs::decodeHash($input['my_parent_id']) : NULL;

        $this->getInputSource()->replace($input);

        return parent::getValidatorInstance();
    }
}
