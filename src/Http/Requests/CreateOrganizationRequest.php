<?php

namespace Litepie\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:organizations,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->wouldCreateCircularReference($value)) {
                        $fail('The selected parent would create a circular reference.');
                    }
                }
            ],
            'type' => [
                'required',
                'string',
                Rule::in(array_keys(config('organization.types', [])))
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:organizations,code'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'manager_id' => [
                'nullable',
                'integer',
                'exists:' . $this->getUserTable() . ',id'
            ],
            'status' => [
                'required',
                'string',
                Rule::in(array_keys(config('organization.statuses', [])))
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'The selected parent organization does not exist.',
            'type.in' => 'The selected type is invalid.',
            'code.unique' => 'This organization code is already taken.',
            'manager_id.exists' => 'The selected manager does not exist.',
            'status.in' => 'The selected status is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'parent_id' => 'parent organization',
            'manager_id' => 'manager',
        ];
    }

    /**
     * Check if selecting this parent would create a circular reference.
     */
    protected function wouldCreateCircularReference($parentId): bool
    {
        // This is a simple check for create request
        // For update requests, this would need more complex logic
        return false;
    }

    /**
     * Get the user table name from config.
     */
    protected function getUserTable(): string
    {
        $userModel = config('organization.user_model', 'App\Models\User');
        return (new $userModel)->getTable();
    }
}
