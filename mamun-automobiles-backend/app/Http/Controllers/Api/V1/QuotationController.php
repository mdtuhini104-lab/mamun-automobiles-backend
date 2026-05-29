<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\QuotationService;
use App\Http\Requests\CreateQuotationRequest;
use App\Http\Requests\ReviseQuotationRequest;
use App\Http\Requests\RecordCustomerApprovalRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    use ApiResponseTrait;

    protected $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    /**
     * List all quotations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quotation::with(['jobCard.customer', 'createdBy']);

        if ($request->has('job_card_id')) {
            $query->where('job_card_id', $request->job_card_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->whereHas('jobCard', function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(15);
        return $this->successResponse(
            QuotationResource::collection($quotations->items()),
            'Quotations retrieved successfully',
            200,
            [
                'current_page' => $quotations->currentPage(),
                'per_page' => $quotations->perPage(),
                'total' => $quotations->total(),
                'last_page' => $quotations->lastPage(),
            ]
        );
    }

    /**
     * Create a new draft quotation.
     */
    public function store(CreateQuotationRequest $request): JsonResponse
    {
        try {
            $quotation = $this->quotationService->createQuotation($request->job_card_id, $request->validated());
            $quotation->load(['items.part', 'createdBy']);
            
            return $this->successResponse(
                new QuotationResource($quotation),
                'Quotation draft created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get details of a quotation with all versions and history.
     */
    public function show(int $id): JsonResponse
    {
        $quotation = Quotation::with([
            'items.part',
            'removedItems.removedBy',
            'approvals.user',
            'createdBy',
            'jobCard.customer',
            'snapshots.createdBy'
        ])->find($id);

        if (!$quotation) {
            return $this->errorResponse('Quotation not found', 404);
        }

        return $this->successResponse(
            new QuotationResource($quotation),
            'Quotation details retrieved successfully'
        );
    }

    /**
     * Revise a quotation to a new version.
     */
    public function revise(ReviseQuotationRequest $request, int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->reviseQuotation($id, $request->validated(), $request->reason);
            $quotation->load(['items.part', 'removedItems', 'createdBy']);
            
            return $this->successResponse(
                new QuotationResource($quotation),
                'Quotation revised successfully to version ' . $quotation->version
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Record customer approval.
     */
    public function approve(RecordCustomerApprovalRequest $request, int $id): JsonResponse
    {
        try {
            $approval = $this->quotationService->recordCustomerApproval($id, $request->validated());
            return $this->successResponse(
                $approval,
                'Customer approval recorded and operational Work Order generated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Remove item from quotation with reasoning.
     */
    public function destroyItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        try {
            $this->quotationService->removeQuotationItem($itemId, $request->reason);
            return $this->successResponse(null, 'Quotation line item removed and audited successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
