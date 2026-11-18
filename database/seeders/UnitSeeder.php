<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Piece',
                'short_name' => 'pcs',
            ],
            [
                'name' => 'Kilogram',
                'short_name' => 'kg',
            ],
            [
                'name' => 'Gram',
                'short_name' => 'grm',
            ],
            [
                'name' => 'Liter',
                'short_name' => 'ltr',
            ],
            [
                'name' => 'Mili-Liter',
                'short_name' => 'ml',
            ],
            [
                'name' => 'Packet',
                'short_name' => 'pkt',
            ],
            [
                'name' => 'Box',
                'short_name' => 'box',
            ],
            [
                'name' => 'Meter',
                'short_name' => 'mtr',
            ],
            [
                'name' => 'Feet',
                'short_name' => 'ft',
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}