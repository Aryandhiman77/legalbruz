<?php

namespace App\Services;

use App\Models\IndianPostalCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IndianPostalCodeImporter
{
    public function getOrImport(string $pincode): Collection
    {
        $pincode = $this->normalizePincode($pincode);

        if (! $pincode) {
            return collect();
        }

        $cachedLocations = IndianPostalCode::query()
            ->where('pincode', $pincode)
            ->get();

        if ($cachedLocations->isNotEmpty()) {
            return $this->backfillCountry($cachedLocations);
        }

        return $this->import($pincode);
    }

    public function import(string $pincode): Collection
    {
        $pincode = $this->normalizePincode($pincode);

        if (! $pincode) {
            return collect();
        }

        $apiKey = config('services.zipcodebase.key');

        if (! $apiKey) {
            throw new \RuntimeException('Zipcodebase API key is not configured.');
        }

        $response = Http::timeout(10)->get(config('services.zipcodebase.search_url'), [
            'apikey' => $apiKey,
            'codes' => $pincode,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Zipcodebase lookup failed for pincode ' . $pincode . '.');
        }

        $locations = collect($this->locationsFromPayload($pincode, $response->json()))
            ->filter(fn (array $location) => filled($location['state']) && filled($location['district']))
            ->unique(fn (array $location) => implode('|', [
                $location['pincode'],
                $location['state'],
                $location['district'],
                $location['city'] ?? '',
            ]))
            ->values();

        return $locations->map(function (array $location) {
            return IndianPostalCode::updateOrCreate(
                [
                    'pincode' => $location['pincode'],
                    'state' => $location['state'],
                    'district' => $location['district'],
                    'city' => $location['city'],
                ],
                [
                    'country' => $location['country'],
                    'country_code' => $location['country_code'],
                    'raw_payload' => $location['raw_payload'],
                ]
            );
        });
    }

    public function importMany(iterable $pincodes): Collection
    {
        return collect($pincodes)
            ->map(fn ($pincode) => $this->normalizePincode((string) $pincode))
            ->filter()
            ->unique()
            ->flatMap(fn (string $pincode) => $this->getOrImport($pincode))
            ->values();
    }

    public function normalizePincode(string $pincode): ?string
    {
        $pincode = substr(preg_replace('/\D/', '', $pincode), 0, 6);

        return strlen($pincode) === 6 ? $pincode : null;
    }

    private function locationsFromPayload(string $pincode, array $payload): array
    {
        $items = data_get($payload, "results.$pincode", []);

        if (! is_array($items)) {
            return [];
        }

        return collect($items)->map(function (array $item) use ($pincode) {
            $city = $this->firstFilled($item, ['city', 'city_en', 'place_name', 'locality']);
            $district = $this->firstFilled($item, ['district', 'district_en', 'county', 'province', 'province_en']);
            $state = $this->firstFilled($item, ['state', 'state_en', 'province', 'province_en', 'region']);

            return [
                'pincode' => $pincode,
                'country' => $this->firstFilled($item, ['country', 'country_en']) ?: 'India',
                'state' => $state,
                'district' => $district,
                'city' => $city ?: $district,
                'country_code' => strtoupper((string) ($item['country_code'] ?? 'IN')),
                'raw_payload' => $item,
            ];
        })->filter(function (array $location) {
            return in_array($location['country_code'], ['IN', 'IND', ''], true);
        })->values()->all();
    }

    private function backfillCountry(Collection $locations): Collection
    {
        $locations->each(function (IndianPostalCode $location) {
            if (! filled($location->country) && in_array($location->country_code, ['IN', 'IND', null, ''], true)) {
                $location->forceFill(['country' => 'India'])->save();
            }
        });

        return $locations->fresh();
    }

    private function firstFilled(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $item[$key] ?? null;

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }
}
