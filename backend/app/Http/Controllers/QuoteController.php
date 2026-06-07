<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuoteRequest;
use App\Http\Resources\QuoteGroupResource;
use App\Http\Resources\QuoteResultResource;
use App\Services\QuoteCalculatorService;
use App\Repositories\QuoteRepository;

class QuoteController extends Controller
{
    private $quoteCalculatorService;
    private $quoteRepository;
    public function __construct(QuoteCalculatorService $quoteCalculatorService, QuoteRepository $quoteRepository)
    {
        $this->quoteCalculatorService = $quoteCalculatorService;
        $this->quoteRepository = $quoteRepository;
    }

    public function index()
    {
        $quotes = $this->quoteRepository->all();

        return QuoteGroupResource::collection($quotes);
    }

    public function calculate(CreateQuoteRequest $request)
    {
        $data = $request->validated();

        $quote = $this->quoteCalculatorService->calculate($data);

        $this->quoteRepository->create($data, $quote);

        return new QuoteResultResource($quote);
    }
}
