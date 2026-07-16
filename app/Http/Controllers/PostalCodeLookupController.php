<?php

namespace App\Http\Controllers;

use App\Models\IndianPostalCode;
use App\Services\IndianPostalCodeImporter;
use Illuminate\Http\JsonResponse;

class PostalCodeLookupController extends Controller
{
    public function show(string $pincode, IndianPostalCodeImporter $importer): JsonResponse
    {
        abort_unless(preg_match('/^\d{6}$/', $pincode), 422, 'Enter a valid 6-digit pincode.');

        try {
            $locations = $importer->getOrImport($pincode);

            if ($locations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Indian postal location found for this pincode.',
                ], 404);
            }
            
            return response()->json($this->formatResponse($pincode, $locations));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch pincode location right now.',
            ], 502);
        }
    }

    private function formatResponse(string $pincode, $locations): array
    {
        return [
            'status' => 'success',
            'pincode' => $pincode,
            'states' => $locations->pluck('state')->filter()->unique()->sort()->values(),
            'districts' => $locations->pluck('district')->filter()->unique()->sort()->values(),
            'locations' => $locations->map(fn (IndianPostalCode $location) => [
                'country' => $location->country,
                'state' => $location->state,
                'district' => $location->district,
                'city' => $location->city,
            ])->values(),
        ];
    }
}
