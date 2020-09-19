<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class DelimitedEmail implements Rule
{
    use ValidatesAttributes;

    private $invalidEmails;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $values = explode(',', $value);
        $parameters = ['rfc', 'spoof'];
        $this->invalidEmails = collect($values)
            ->filter(function ($value) use ($attribute, $parameters) {
                return !$this->validateEmail($attribute, $value, $parameters);
            })
            ->implode(', ');
        return !$this->invalidEmails;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute に指定した次のメールアドレスは不正です。: ' . $this->invalidEmails;
    }
}
