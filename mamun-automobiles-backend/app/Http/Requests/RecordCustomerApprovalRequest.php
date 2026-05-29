<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCustomerApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:approved,rejected,partially_approved,changes_requested',
            'approved_by' => 'required|string|max:255',
            'signature_path' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'approved_items' => 'nullable|array',
            'approved_items.*' => 'integer|exists:quotation_items,id',
            'rejected_items' => 'nullable|array',
            'rejected_items.*' => 'integer|exists:quotation_items,id',
        ];
    }
}
