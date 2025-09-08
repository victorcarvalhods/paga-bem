<?php

namespace App\Http\Controllers\Wallet;

use App\Actions\Wallet\StoreWalletAction;
use App\DataTransferObjects\Wallet\StoreWalletDTO;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreWalletController extends Controller
{
    public function __construct(private StoreWalletAction $action)
    {
        $this->action = $action;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'balance' => 'required|numeric|min:0',
            'wallet_type' => 'required|string|in:' . implode(',', Wallet::WALLET_TYPES),
        ]);

        $dto = StoreWalletDTO::fromArray($data);

        $wallet = $this->action->handle($dto);

        return response()->json($wallet->toArray(), Response::HTTP_CREATED);
    }
}
