<?php

namespace Saidtech\Routereseller\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Models\PromoCode;

class WHMCSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createProducts();
        $this->createPaymentGateways();
        $this->createPromoCodes();

        $this->command->info('WHMCS-like data seeded successfully!');
    }

    /**
     * Create hosting products.
     */
    private function createProducts(): void
    {
        $products = [
            [
                'name' => 'Starter Hosting',
                'slug' => 'starter-hosting',
                'description' => 'Perfect for personal websites and small blogs.',
                'type' => 'hosting',
                'payment_type' => 'recurring',
                'setup_fee' => 0.00,
                'monthly_price' => 4.99,
                'annually_price' => 49.99,
                'features' => ['10 GB Storage', '100 GB Bandwidth', 'Free SSL'],
                'is_featured' => false,
                'is_active' => true,
                'auto_setup' => 'payment',
                'sort_order' => 1
            ],
            [
                'name' => 'Professional Hosting',
                'slug' => 'professional-hosting',
                'description' => 'Ideal for growing businesses.',
                'type' => 'hosting',
                'payment_type' => 'recurring',
                'setup_fee' => 0.00,
                'monthly_price' => 9.99,
                'annually_price' => 99.99,
                'features' => ['50 GB Storage', '500 GB Bandwidth', 'Free SSL'],
                'is_featured' => true,
                'is_active' => true,
                'auto_setup' => 'payment',
                'sort_order' => 2
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }

    private function createPaymentGateways(): void
    {
        PaymentGateway::create([
            'name' => 'Stripe',
            'gateway_name' => 'stripe',
            'display_name' => 'Credit/Debit Card',
            'description' => 'Pay securely with your card via Stripe.',
            'supports_recurring' => true,
            'supports_refunds' => true,
            'percentage_fee' => 2.9,
            'fixed_fee' => 0.30,
            'is_active' => true,
            'is_visible' => true,
            'sort_order' => 1
        ]);
    }

    private function createPromoCodes(): void
    {
        PromoCode::create([
            'code' => 'WELCOME50',
            'description' => '50% off your first order',
            'type' => 'percentage',
            'value' => 50.00,
            'applies_to' => 'all',
            'max_uses' => 1000,
            'current_uses' => 0,
            'new_clients_only' => true,
            'is_active' => true
        ]);
    }
}
