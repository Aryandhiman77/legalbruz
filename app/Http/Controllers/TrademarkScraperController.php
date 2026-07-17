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
        ]);

        $keyword = $request->keyword;

        $results = $scraper->scrapeWithoutBrowser($keyword);

        if (count($results) === 0) {
            return response()->json([
                'success' => true,
                'message' => 'No matching record was found in the available search data.',
                'keyword' => $keyword,
                'total' => 0,
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'keyword' => $keyword,
            'total' => count($results),
            'data' => $results,
        ]);
    }
}
