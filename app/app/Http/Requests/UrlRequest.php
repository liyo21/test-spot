<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        Log::info("[UrlRequest][rules] Inicio de la validación");

        return [
            'url' => 'required|url:http,https'
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

    protected function failedValidation(Validator $validator)
    {
        Log::info("[UrlRequest][failedValidation] Falló la validación la validación, se retorna respuesta");

        throw new HttpResponseException(
            response()->json([
                'status'    => 'NOK',
                'message'   => $validator->errors(),
                'response'  => []
            ], 422)
        );
    }
}
