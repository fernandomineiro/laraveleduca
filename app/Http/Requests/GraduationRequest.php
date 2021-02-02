<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GraduationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'profile_id'        => 'required',
            'course_id'         => 'required',
            'start_date'        => 'required|date_format:d/m/Y',
            'final_date'        => 'required|date_format:d/m/Y',
        ];
    }
}
