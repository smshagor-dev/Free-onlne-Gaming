<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use RuntimeException;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // MIT-licensed annexare/Countries dataset pinned in source control.
        // No network connection required during deployment.
        $path = database_path('seeders/data/countries.json');
        $countries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($countries) || count($countries) < 240) {
            throw new RuntimeException('The country seed reference dataset is incomplete.');
        }

        foreach ($countries as $iso => $country) {
            if (!preg_match('/^[A-Z]{2}$/D', (string) $iso)
                || !preg_match('/^[A-Z]{3}$/D', (string) ($country['iso3'] ?? ''))) {
                throw new RuntimeException('Invalid ISO country code in seed data.');
            }

            // Match on ISO-2, not numeric id, to preserve existing user foreign keys.
            $existing = Country::query()->where('iso', $iso)->first();
            if ($existing === null) {
                Country::create([
                    'name' => $country['name'],
                    'iso' => $iso,
                    'iso3' => $country['iso3'],
                    'dial' => $country['dial'],
                    'currency' => $country['currency'],
                    'currency_name' => $country['currency_name'],
                    'currency_symbol' => $country['currency_symbol'],
                    'status' => true,
                ]);

                continue;
            }

            // Only enrich missing fields. Preserve manual currency edits, disabled
            // countries, existing IDs and all other production information.
            $patch = [];
            foreach (['iso3', 'dial', 'currency', 'currency_name', 'currency_symbol'] as $field) {
                if (blank($existing->{$field}) && filled($country[$field] ?? null)) {
                    $patch[$field] = $country[$field];
                }
            }

            // Fix a known typo in the old bundled seed while not touching other
            // names (which site administrators may have customized).
            if ($iso === 'LV' && $existing->name === 'Latitude') {
                $patch['name'] = 'Latvia';
            }
            if ($patch !== []) {
                $existing->update($patch);
            }
        }
    }
}
