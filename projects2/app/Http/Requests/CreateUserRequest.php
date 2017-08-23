<?php

namespace App\Http\Requests;

use Gate;
use App\Http\Requests\Request;

class CreateUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        return $this->user()->isAdmin();
        //return Gate::allows('edit_users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'surname' => 'required',
            'forenames' => 'required'
        ];
    }
}
