<?php

namespace App\Http\Controllers\Transaction;

use App\Actions\Transaction\ProcessTransactionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class StoreTransactionController extends Controller
{
    public function __construct(private ProcessTransactionAction $action) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->action->handle($request->toDTO());

        return response()->json($transaction, Response::HTTP_CREATED);
    }
}
