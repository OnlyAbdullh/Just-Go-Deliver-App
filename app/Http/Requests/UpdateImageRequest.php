<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class UpdateImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $image = $this->route('image');

        $ownerStore = $image->store->user_id;

        return auth()->user()->hasRole('store_admin') && $ownerStore === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,bmp|max:2048'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->toArray())
            ->map(fn($error) => $error[0]) // Get only the first error for each field
            ->toArray();

        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.validation_error'), $errors, 400)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.not_authorized_to_add_product'), [], 403)
        );
    }
}
