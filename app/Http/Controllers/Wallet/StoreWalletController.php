<?php

namespace App\Http\Controllers\Wallet;

use App\Actions\Wallet\StoreWalletAction;
use App\DataTransferObjects\Wallet\StoreWalletDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreWalletRequest;
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
    public function __invoke(StoreWalletRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = StoreWalletDTO::fromArray($data);

        $wallet = $this->action->handle($dto);

        return response()->json($wallet->toArray(), Response::HTTP_CREATED);
    }
}
