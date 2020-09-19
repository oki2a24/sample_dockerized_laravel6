<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DelimitedMax implements Rule
{
    private $maxCount;
    private $delimiter;

    /**
     * Create a new rule instance.
     *
     * @param  int  $maxCount
     * @param  string  $delimiter
     * @return void
     */
    public function __construct(int $maxCount, string $delimiter = ',')
    {
        $this->maxCount = $maxCount;
        $this->delimiter = $delimiter;
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
        $values = explode($this->delimiter, $value);
        return count($values) <= $this->maxCount;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute に指定できる email は最大 ' . $this->maxCount . ' 個です。';
    }
}
