<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PositionRequest extends FormRequest
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
        $rules = [
            'name' => 'required|unique:positions,name,NULL,name,deleted_at,NULL|max:10',
            'level' => 'numeric|integer|max:99',
            'is_public' => 'in:0,1',
            'is_manager' => 'in:0,1',
            'brands' => 'array',
        ];
        if ($this->getMethod() === 'PATCH') {
            $rules = array_merge($rules, [
                'name' => [
                    'required',
                    unique_validator('positions'),
                ],
            ]);
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '职位名称',
            'level' => '职级',
            'brands' => '关联品牌',
            'is_public' => '是否公共职位',
        ];
    }
}
