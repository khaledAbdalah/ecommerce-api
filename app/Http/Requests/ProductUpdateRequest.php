<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize (): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules (): array
    {
        return [
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:png,jpg,jpeg,webp',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:published,draft',
            'featured' => 'required|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id'
        ];
    }
}