<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_id'     =>  'nullable|integer|exists:shipping_addresses,id',
            'country'        =>  'required_without:address_id|string|max:20',
            'address_line1'  =>  'required_without:address_id|string|max:255',
            'address_line2'  =>  'nullable|string|max:255',
            'city'           =>  'required_without:address_id|string|max:255',
            'state'          =>  'required_without:address_id|string|max:20',
            'postal_code'    =>  'required_without:address_id|string|max:10',
            'phone'          =>  'required_without:address_id|phone:AUTO',
            'payment_method' =>  'required|in:cash,card',
            'notes'          =>  'nullable|string',
        ];
    }
}
