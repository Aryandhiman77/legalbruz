<?php

namespace App\Http\Controllers;

use App\Services\QuickCompanyTrademarkScraper;
use Illuminate\Http\Request;

class TrademarkScraperController extends Controller
{
    public function scrape(Request $request, QuickCompanyTrademarkScraper $scraper)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $keyword = $request->keyword;
        $limit = $request->limit ?? 20;

        try {
            $results = $scraper->scrapeTrademark($keyword, $limit);

            return response()->json([
                'success' => true,
                'keyword' => $keyword,
                'total' => count($results),
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Trademark scraping failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
