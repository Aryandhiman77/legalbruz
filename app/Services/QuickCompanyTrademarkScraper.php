<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class QuickCompanyTrademarkScraper
{
    public function scrapeWithoutBrowser(string $keyword): array
    {
        $sourceUrl = 'https://www.quickcompany.in/trademarks?q=' . urlencode($keyword);

        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
            ->get($sourceUrl);

        if (!$response->successful()) {
            return [];
        }

        return $this->extractData($response->body(), $keyword, $sourceUrl);
    }

    private function extractData(string $html, string $keyword, string $sourceUrl): array
    {
        $crawler = new Crawler($html);

        $results = [];
        $seenIds = [];

        $crawler->filter('div, article, section')->each(function (Crawler $node) use (&$results, &$seenIds, $keyword, $sourceUrl) {
            $text = $this->cleanText($node->text(''));

            if (
                !str_contains($text, 'ID:') ||
                !str_contains($text, 'Class:') ||
                !preg_match('/Registered|Objected|Accepted|Advertised|Abandoned|Refused|Opposed|Removed/i', $text)
            ) {
                return;
            }

            $applicationId = $this->match('/ID:\s*([0-9]+)/', $text);

            if (!$applicationId || in_array($applicationId, $seenIds, true)) {
                return;
            }

            $seenIds[] = $applicationId;

            $applicationDate = $this->match('/(\d{1,2}\s+[A-Za-z]{3}\s+\d{4})/', $text);

            $trademarkClass = $this->match('/Class:\s*([0-9]+)/', $text);

            $status = $this->match(
                '/\b(Registered|Objected|Accepted|Advertised|Abandoned|Refused|Opposed|Removed)\b/i',
                $text
            );

            $type = $this->match(
                '/\b(Device|Word|Label|Logo|Wordmark)\b/i',
                $text
            );

            $headingText = $this->match(
                '/ID:\s*[0-9]+\s+(.+?)\s+\[Class\s*:/',
                $text
            );

            $headingText = $this->cleanText(
                preg_replace(
                    [
                        '/Registered/i',
                        '/Class:\s*\d+/i',
                        '/Device|Word|Label|Logo|Wordmark/i',
                    ],
                    '',
                    $headingText ?? ''
                )
            );

            $parts = $headingText ? explode(' ', $headingText) : [];

            $trademarkName = $parts[0] ?? '';
            $proprietor = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

            $description = $this->match(
                '/\[Class\s*:\s*[0-9]+\]\s*(.+)/',
                $text
            );

            $imageUrl = '';

            $img = $node->filter('img');

            if ($img->count()) {
                $imageUrl = $img->first()->attr('src') ?: $img->first()->attr('data-src') ?: '';

                if ($imageUrl && str_starts_with($imageUrl, '/')) {
                    $imageUrl = 'https://www.quickcompany.in' . $imageUrl;
                }
            }

            $results[] = [
                'search_keyword' => $keyword,
                'application_id' => $applicationId,
                'application_date' => $applicationDate ?? '',
                'trademark_name' => $trademarkName,
                'proprietor' => $proprietor,
                'status' => $status ?? '',
                'class' => $trademarkClass ?? '',
                'type' => $type ?? '',
                'description' => $this->cleanText($description ?? ''),
                'image_url' => $imageUrl,
                'source_url' => $sourceUrl,
            ];
        });

        return $results;
    }

    private function cleanText(?string $text = ''): string
    {
        return trim(preg_replace('/\s+/', ' ', $text ?? ''));
    }

    private function match(string $pattern, string $text): ?string
    {
        preg_match($pattern, $text, $matches);

        return isset($matches[1]) ? $this->cleanText($matches[1]) : null;
    }
}