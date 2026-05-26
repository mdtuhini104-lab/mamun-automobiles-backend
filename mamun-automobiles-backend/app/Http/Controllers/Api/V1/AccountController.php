<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponseTrait;

    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Account::class);

        $accounts = $this->accountService->listAccounts($request->all());
        
        $meta = [
            'current_page' => $accounts->currentPage(),
            'per_page' => $accounts->perPage(),
            'total' => $accounts->total(),
            'last_page' => $accounts->lastPage(),
        ];
        
        return $this->successResponse(AccountResource::collection($accounts->items()), 'Accounts retrieved successfully', 200, $meta);
    }

    public function store(CreateAccountRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Account::class);

        $account = $this->accountService->createAccount($request->validated());
        return $this->successResponse(new AccountResource($account), 'Account created successfully', 201);
    }

    /**
     * Get account details.
     */
    public function show(int $id): JsonResponse
    {
        $account = $this->accountService->getAccount($id);
        
        if (!$account) {
            return $this->errorResponse('Account not found', 404);
        }
        
        $this->authorize('view', $account);
        
        return $this->successResponse(new AccountResource($account), 'Account details retrieved successfully');
    }

    /**
     * Update an account.
     */
    public function update(UpdateAccountRequest $request, int $id): JsonResponse
    {
        $account = $this->accountService->getAccount($id);
        
        if (!$account) {
            return $this->errorResponse('Account not found', 404);
        }

        $this->authorize('update', $account);

        $updated = $this->accountService->updateAccount($id, $request->validated());
        
        if (!$updated) {
            return $this->errorResponse('Update failed', 400);
        }
        
        $account = $this->accountService->getAccount($id);
        return $this->successResponse(new AccountResource($account), 'Account updated successfully');
    }

    /**
     * Delete an account.
     */
    public function destroy(int $id): JsonResponse
    {
        $account = $this->accountService->getAccount($id);
        
        if (!$account) {
            return $this->errorResponse('Account not found', 404);
        }

        $this->authorize('delete', $account);

        $deleted = $this->accountService->deleteAccount($id);
        
        if (!$deleted) {
            return $this->errorResponse('Delete failed', 400);
        }
        
        return $this->successResponse(null, 'Account deleted successfully');
    }
}
