<?php

namespace App\Http\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Yo;

class ChangeAdminPassword extends FormRequest
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
      'password' => ['required', 'between:6,20'],
    ];
  }

  public function messages()
  {
    return [
      'password.required' => 100009,
      'password.between' => 100010,
    ];
  }

  public function failedValidation(Validator $validator)
  {
    Yo::error_echo($validator->errors()->first());
  }
}
