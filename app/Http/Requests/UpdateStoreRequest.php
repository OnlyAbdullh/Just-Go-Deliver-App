<?php

namespace App\Http\Requests;

use App\Helpers\JsonResponseHelper;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $store = $this->route('store');
        return $store == auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo'=>'sometimes|image|mimes:jpg,png,jpeg',
            'location'=>'sometimes|string',
            'description'=>'sometimes',
            'name'=>'sometimes',
        ];
    }
    protected function failedAuthorization():JsonResponse
    {
        return JsonResponseHelper::errorResponse('You are not authorized to update this store.',[],403);
    }
}
