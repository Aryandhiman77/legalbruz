<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Application;
use App\Services\IndianPostalCodeImporter;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('postal-codes:import {--codes=* : One or more 6-digit Indian pincodes} {--from-applications : Import pincodes already saved on trademark applications}', function (IndianPostalCodeImporter $importer) {
    $codes = collect($this->option('codes'))
        ->flatMap(fn ($value) => preg_split('/[\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY));

    if ($this->option('from-applications') || $codes->isEmpty()) {
        $applicationCodes = Application::query()
            ->get(['members_details'])
            ->flatMap(function (Application $application) {
                return [
                    data_get($application->members_details, 'trademark_applicant_details.pin_code'),
                    data_get($application->members_details, 'details_of_signatory.pin_code'),
                    data_get($application->members_details, 'details_of_co_applicant_or_partners.pin_code'),
                ];
            });

        $codes = $codes->merge($applicationCodes);
    }

    $codes = $codes
        ->map(fn ($code) => $importer->normalizePincode((string) $code))
        ->filter()
        ->unique()
        ->values();

    if ($codes->isEmpty()) {
        $this->warn('No valid pincodes found to import.');
        return 0;
    }

    $this->info('Importing ' . $codes->count() . ' pincode(s)...');
    $createdOrUpdated = 0;

    foreach ($codes as $pincode) {
        try {
            $locations = $importer->getOrImport($pincode);
            $createdOrUpdated += $locations->count();
            $this->line($pincode . ': ' . $locations->count() . ' location(s) stored.');
        } catch (Throwable $e) {
            $this->error($pincode . ': ' . $e->getMessage());
        }
    }

    $this->info('Done. Stored ' . $createdOrUpdated . ' postal location row(s).');
    return 0;
})->purpose('Import Indian postal code country, state, district and city data from Zipcodebase.');
