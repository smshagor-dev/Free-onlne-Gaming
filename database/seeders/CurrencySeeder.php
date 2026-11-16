<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        // Pinned MIT dataset from annexare/Countries, never fetched at runtime.
        $path = database_path('seeders/data/currencies.json');
        $currencies = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($currencies) || count($currencies) < 150) {
            throw new RuntimeException('The currency seed reference dataset is incomplete.');
        }

        $now = now();
        $rows = [];
        foreach ($currencies as $code => $value) {
            if (!preg_match('/^[A-Z]{3}$/D', (string) $code)) {
                throw new RuntimeException('Invalid ISO currency code in seed data.');
            }
            $rows[] = [
                'code' => $code,
                'name' => $value['name'],
                'symbol' => $value['symbol'],
                'numeric_code' => $value['numeric'] ?: null,
                'decimals' => $value['decimals'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // No overwrite of existing/admin-maintained currency rows on re-seed.
        foreach (array_chunk($rows, 80) as $chunk) {
            DB::table('currencies')->insertOrIgnore($chunk);
        }
    }
}
