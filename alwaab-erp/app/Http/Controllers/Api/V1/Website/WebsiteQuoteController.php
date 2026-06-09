<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\StoreWebsiteQuoteRequest;
use App\Services\WebsiteQuoteService;
use Illuminate\Http\JsonResponse;

class WebsiteQuoteController extends Controller
{
    public function __construct(private WebsiteQuoteService $websiteQuotes) {}

    public function store(StoreWebsiteQuoteRequest $request): JsonResponse
    {
        $result = $this->websiteQuotes->submit($request->validated());

        return response()->json([
            'message' => __('quotations.website_received'),
            'rfq' => [
                'id' => $result['rfq']->id,
                'number' => $result['rfq']->number,
            ],
            'invoice' => [
                'id' => $result['invoice']->id,
                'number' => $result['invoice']->number,
            ],
        ], 201);
    }
}
