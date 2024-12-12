<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class createProductRequest extends FormRequest
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
            'name' => 'required',
            'category_name' => 'required',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,bmp',
            'sub_images' => 'required|array|min:1',
            'sub_images.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            'price' => 'required',
            'quantity' => 'required',
            'description' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->toArray())
            ->map(fn($error) => $error[0]) // Get only the first error for each field
            ->toArray();

        throw new HttpResponseException(
            JsonResponseHelper::errorResponse(__('messages.validation_error'), $errors,400)
        );
    }

    protected function failedAuthorization(): JsonResponse
    {
        return JsonResponseHelper::errorResponse(__('messages.not_authorized_to_add_product'), [], 403);
    }
}
