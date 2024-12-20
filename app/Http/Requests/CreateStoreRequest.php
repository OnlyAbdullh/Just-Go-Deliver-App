<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\JsonResponseHelper;

class CreateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => 'required|image|mimes:jpg,png,jpeg',
            'location_ar' => 'sometimes|string',
            'location_en' => 'sometimes|string',
            'description_ar' => 'sometimes|required_with:description_en',
            'name_ar' => 'required|unique:stores,name_ar',
            'description_en' => 'sometimes|required_with:description_ar',
            'name_en' => 'required|unique:stores,name_en'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->toArray())
            ->map(fn($error) => $error[0]) // Get only the first error for each field
            ->toArray();

        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.validation_error'), $errors)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.store_admin_only_create'), [], 403)
        );
    }
    
}
