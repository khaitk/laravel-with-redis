<?php

namespace Laravel\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $batchSize = 5000;
        $totalRecords = 1000000;
        $now = now();

        for ($i = 0; $i < $totalRecords; $i += $batchSize) {
            $data = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $id = $i + $j + 1;
                $data[] = [
                    'name' => "Product #{$id}",
                    'price' => random_int(10000, 500000),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('products')->insert($data);
        }
    }
}
