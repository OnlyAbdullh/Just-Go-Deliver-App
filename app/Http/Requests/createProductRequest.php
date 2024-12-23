<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class createProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $store = $this->route('store');

        if (auth()->user()->hasRole('store_admin') && $store->user_id === auth()->id()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_ar' => 'required',
            'name_en' => 'required',
            'category_name_ar' => 'required',
            'category_name_en' => 'required',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,bmp|max:2048',
            'sub_images' => 'required|array|min:1',
            'sub_images.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            'price' => 'required',
            'quantity' => 'required',
            'description_ar' => 'required',
            'description_en' => 'required',
        ];
    }

    public function attributes(): array
    {
        return [
            'name_ar' => __('messages.name_ar'),
            'name_en' => __('messages.name_en'),
            'category_name_ar' => __('messages.category_name_ar'),
            'category_name_en' => __('messages.category_name_en'),
            'main_image' => __('messages.main_image'),
            'sub_images' => __('messages.sub_images'),
            'sub_images.*' => __('messages.sub_images_item'),
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
            JsonResponseHelper::errorResponse(__('messages.not_authorized_to_add_product'), [], 403)
        );
    }
}
