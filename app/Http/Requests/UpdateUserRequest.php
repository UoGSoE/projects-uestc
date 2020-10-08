<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Gate;

class UpdateUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('edit_users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|unique:users,username,'.$this->id,
            'email' => 'required|email|unique:users,email,'.$this->id,
            'surname' => 'required',
            'forenames' => 'required',
        ];
    }
}
