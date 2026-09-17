<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

final class ShippingQuoteUiController extends Controller
{
    public function index(): View
    {
        return view('shipping.quotes.index', [
            'prefillQuoteId' => null,
            'autoFetchQuote' => false,
        ]);
    }

    public function show(string $id): View
    {
        return view('shipping.quotes.index', [
            'prefillQuoteId' => $id,
            'autoFetchQuote' => true,
        ]);
    }
}
