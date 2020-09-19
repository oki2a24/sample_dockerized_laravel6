<?php

namespace App\Http\Requests;

use App\Rules\DelimitedEmail;
use App\Rules\DelimitedMax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                'email:rfc,spoof',
                Rule::unique('users')->ignore(Auth::id()),
            ],
            'cc_emails' => [
                'nullable',
                'max:2550',
                new DelimitedMax(10),
                new DelimitedEmail(),
            ],
            'zip' => 'nullable|regex:/^\d{7}$/',
        ];
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'cc_emails' => 'CCメールアドレス',
        ];
    }
}
