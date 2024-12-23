<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $store = $this->route('store');

        return auth()->user()->hasRole('store_admin') && $store->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => 'sometimes|image|mimes:jpg,png,jpeg',
            'location_ar' => 'sometimes|string',
            'location_en' => 'sometimes|string',
            'description_ar' => 'sometimes',
            'name_ar' => 'sometimes|:stores,name_ar',
            'description_en' => 'sometimes',
            'name_en' => 'sometimes|unique:stores,name_en',
        ];
    }

    public function attributes(): array
    {
        return [
            'logo' => __('messages.logo'),
            'location_ar' => __('messages.location_ar'),
            'location_en' => __('messages.location_en'),
            'description_ar' => __('messages.description_ar'),
            'name_ar' => __('messages.name_ar'),
            'description_en' => __('messages.description_en'),
            'name_en' => __('messages.name_en'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->toArray())
            ->map(fn ($error) => $error[0]) // Get only the first error for each field
            ->toArray();

        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.validation_error'), $errors, 400)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.store_admin_only_update'), [], 403)
        );
    }
}
