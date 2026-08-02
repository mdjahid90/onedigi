<?php

namespace Database\Seeders;

use App\Services\PaymentGatewayService;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        PaymentGatewayService::syncSupportedGateways();
    }
}
