<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use App\Models\TrademarkPricing;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home', [
            'trademarkPricingPlans' => TrademarkPricing::activePlans(),
            'customerReviews' => CustomerReview::homepageReviews(),
        ]);
    }
}
