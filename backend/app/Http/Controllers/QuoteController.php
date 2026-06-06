<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuoteRequest;
use App\Http\Resources\QuoteResultResource;
use App\Services\QuoteCalculatorService;

class QuoteController extends Controller
{
    private QuoteCalculatorService $quoteCalculatorService;

    public function __construct(QuoteCalculatorService $quoteCalculatorService)
    {
        $this->quoteCalculatorService = $quoteCalculatorService;
    }

    public function calculate(CreateQuoteRequest $request)
    {
        $data = $request->validated();

        $quote = $this->quoteCalculatorService->calculate($data);

        return new QuoteResultResource($quote);
    }
}
