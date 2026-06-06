<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuoteCalculatorService;

class QuoteController extends Controller
{
    private QuoteCalculatorService $quoteCalculatorService;

    public function __construct(QuoteCalculatorService $quoteCalculatorService)
    {
        $this->quoteCalculatorService = $quoteCalculatorService;
    }

    public function calculate(Request $request)
    {
       
    }
}
