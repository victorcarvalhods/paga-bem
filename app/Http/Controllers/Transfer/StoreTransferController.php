<?php

namespace App\Http\Controllers\Transfer;

use App\Actions\Transfer\ProcessTransferAction;
use App\DataTransferObjects\Transfer\TransferDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Models\Transfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreTransferController extends Controller
{
    public function __construct(private ProcessTransferAction $action) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreTransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['payer_id'] = $validated['payer'];
        $validated['payee_id'] = $validated['payee'];

        $dto = TransferDTO::fromArray($validated);

        $transfer = $this->action->handle($dto);

        return response()->json($transfer, Response::HTTP_CREATED);
    }
}
