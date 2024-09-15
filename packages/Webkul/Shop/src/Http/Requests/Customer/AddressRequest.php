<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Core\Rules\Address;
use Webkul\Core\Rules\AlphaNumericSpace;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Rules\VatIdRule;

class AddressRequest extends FormRequest
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
            'company_name' => [new AlphaNumericSpace],
            // 'first_name'   => ['required', new AlphaNumericSpace],
            // 'last_name'    => ['required', new AlphaNumericSpace],
            'first_name'   => ['required'],
            'last_name'    => ['required'],
            'address1'     => ['required', 'array'],
            'address1.*'   => [new Address],
            'country'      => core()->isCountryRequired() ? ['required'] : [],
            'state'        => core()->isStateRequired() ? ['required'] : [],
            'city'         => ['required', 'string'],
            'postcode'     => core()->isPostCodeRequired() ? ['required', 'numeric'] : ['numeric'],
            'phone'        => ['required', new PhoneNumber],
            'vat_id'       => [new VatIdRule()],
        ];
    }

    /**
     * Attributes.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'address1.*' => 'address',
        ];
    }
}
