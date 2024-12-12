<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

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
            'location' => 'sometimes|string',
            'description' => 'sometimes',
            'name' => 'sometimes',
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
        return JsonResponseHelper::errorResponse(__('messages.store_update_unauthorized'), [], 403);
    }
}
