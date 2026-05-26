<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponseTrait;

    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Transaction::class);

        $transactions = $this->transactionService->listTransactions($request->all());
        
        $meta = [
            'current_page' => $transactions->currentPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
            'last_page' => $transactions->lastPage(),
        ];
        
        return $this->successResponse(TransactionResource::collection($transactions->items()), 'Transactions retrieved successfully', 200, $meta);
    }

    public function store(CreateTransactionRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Transaction::class);

        try {
            $transaction = $this->transactionService->createTransaction($request->validated());
            return $this->successResponse(new TransactionResource($transaction), 'Transaction created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransaction($id);
        
        if (!$transaction) {
            return $this->errorResponse('Transaction not found', 404);
        }
        
        $this->authorize('view', $transaction);
        
        return $this->successResponse(new TransactionResource($transaction), 'Transaction details retrieved successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransaction($id);
        
        if (!$transaction) {
            return $this->errorResponse('Transaction not found', 404);
        }

        $this->authorize('update', $transaction);

        $updated = $this->transactionService->updateTransaction($id, $request->all());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $transaction = $this->transactionService->getTransaction($id);
        return $this->successResponse(new TransactionResource($transaction), 'Transaction updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransaction($id);
        
        if (!$transaction) {
            return $this->errorResponse('Transaction not found', 404);
        }

        $this->authorize('delete', $transaction);

        try {
            $deleted = $this->transactionService->deleteTransaction($id);
            
            if (!$deleted) {
                return $this->errorResponse('Delete failed', 400);
            }
            
            return $this->successResponse(null, 'Transaction deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
