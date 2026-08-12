<?php

namespace App\Http\Requests\Api\AiAgent;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @property string $message
 * @property string $sessionId
 */
class AiAgentSendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:300000',
            'sessionId' => 'required|string|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'A message is required.',
            'message.max' => 'The message cannot exceed 300,000 characters.',
            'sessionId.required' => 'A session ID is required.',
        ];
    }
}
