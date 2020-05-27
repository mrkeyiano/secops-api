<?php

namespace App\Http\Requests\Verify;

use Illuminate\Foundation\Http\FormRequest;

class BvnRequest extends FormRequest
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
            'bvn' => 'required_without_all:accountNumber|digits_between:11,12',
            'accountNumber' => 'required_without_all:bvn|digits_between:10,11',
        ];
    }

    public function messages(): array
    {
        return [
            'bvn.required' => 'Valid Bvn must be supplied',
            'accountNumber.required' => 'Valid Account Number must be supplied',
        ];
    }
}
