<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class updateProductRequest extends FormRequest
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
            'price' => 'sometimes|numeric|between:0,99999.99',
            'quantity' => 'sometimes|min:0',
            'description_ar' => 'sometimes|required_with:description_en',
            'description_en' => 'sometimes|required_with:description_ar',
            'main_image' => 'sometimes|image',
        ];
    }

    public function attributes(): array
    {
        return [
            'main_image' => __('messages.main_image'),
            'price' => __('messages.price'),
            'quantity' => __('messages.quantity'),
            'description_ar' => __('messages.description_ar'),
            'description_en' => __('messages.description_en'),
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
            JsonResponseHelper::errorResponse(__('messages.not_authorized_to_update_product'), [], 403)
        );
    }
}
