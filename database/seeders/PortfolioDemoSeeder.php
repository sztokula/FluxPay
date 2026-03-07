<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortfolioDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'portfolio@example.test'],
            [
                'name' => 'Portfolio User',
                'password' => bcrypt('password123'),
            ]
        );

        Customer::query()->firstOrCreate(
            ['email' => $user->email],
            [
                'name' => $user->name,
                'user_id' => $user->id,
            ]
        );

        Product::query()->firstOrCreate(
            ['name' => 'Developer T-Shirt'],
            [
                'description' => 'Simple cotton T-shirt for developers',
                'price' => 2900,
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        Product::query()->firstOrCreate(
            ['name' => 'Console Mug'],
            [
                'description' => 'Large ceramic mug with artisan logo',
                'price' => 1900,
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        $plan = Plan::query()->firstOrCreate(
            ['name' => 'Starter'],
            [
                'description' => 'Starter monthly plan',
                'is_active' => true,
            ]
        );

        Price::query()->firstOrCreate(
            ['plan_id' => $plan->id, 'amount' => 1200, 'currency' => 'USD'],
            [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_days' => 7,
                'is_active' => true,
            ]
        );
    }
}
