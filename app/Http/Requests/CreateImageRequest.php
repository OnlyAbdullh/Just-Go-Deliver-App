<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateImageRequest extends FormRequest
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
            'images' => 'required|array', // Ensure it's an array for multiple images
            'images.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'images' => __('messages.images'),
            'images.*' => __('messages.images.*'),
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
            JsonResponseHelper::errorResponse(__('messages.not_authorized_to_add_image'), [], 403)
        );
    }
}
