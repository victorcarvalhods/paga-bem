<?php

namespace App\Http\Requests\Transaction;

use App\DataTransferObjects\Transaction\TransactionDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0.01'],
            'payer' => ['required', 'integer', 'exists:wallets,id'],
            'payee' => ['required', 'integer', 'exists:wallets,id'],
        ];
    }

    public function toDTO(): TransactionDTO
    {
        $data = $this->validated();
        return new TransactionDTO(
            value: floatval($data['value']),
            payer: $data['payer'],
            payee: $data['payee'],
        );
    }

}
