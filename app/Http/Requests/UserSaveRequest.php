<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use Illuminate\Validation\Rule;

class UserSaveRequest extends FormRequest
{
    use ValidatesAttributes;

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
                function ($attribute, $value, $fail) {
                    $values = explode(',', $value);
                    if (count($values) > 10) {
                        $fail(trans($attribute . ' に指定できる email は最大 10 個です。'));
                    }
                },
                function ($attribute, $value, $fail) {
                    $values = explode(',', $value);
                    $parameters = ['rfc', 'spoof'];
                    $invalidEmails = collect($values)
                        ->filter(function ($value) use ($attribute, $parameters) {
                            return !$this->validateEmail($attribute, $value, $parameters);
                        })
                        ->implode(', ');
                    if ($invalidEmails) {
                        $fail($attribute . ' に指定した次のメールアドレスは不正です。: ' . $invalidEmails);
                    }
                },
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
