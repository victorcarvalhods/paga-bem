<?php

namespace App\Http\Requests\Wallet;

use App\DataTransferObjects\Wallet\WalletDTO;
use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'balance' => 'required|numeric|min:0',
            'wallet_type' => 'required|string|in:' . implode(',', Wallet::WALLET_TYPES),
        ];
    }

    public function toDTO(): WalletDTO
    {
        $data = $this->validated();
        return WalletDTO::fromArray([
            'id' => null,
            'userId' => $data['user_id'],
            'balance' => floatval($data['balance']),
            'walletType' => $data['wallet_type'],
        ]);
    }
}
