<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UrlRequest extends FormRequest
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
            'url' => 'required|url:http,https',
        ];
    }

    /**
     * Get the error messages for the defined rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'url.required'  => 'El campo URL es obligatorio.',
            'url.url'       => 'Por favor, ingresa una URL válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('url'))) {
            $this->merge(['url' => trim($this->input('url'))]);
        }
    }
}
