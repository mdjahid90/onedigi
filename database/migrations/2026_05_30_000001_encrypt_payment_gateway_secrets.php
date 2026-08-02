<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_gateways')
            ->select(['id', 'api_key', 'secret_key'])
            ->orderBy('id')
            ->chunkById(100, function ($gateways) {
                foreach ($gateways as $gateway) {
                    DB::table('payment_gateways')
                        ->where('id', $gateway->id)
                        ->update([
                            'api_key' => $this->encryptedOrNull($gateway->api_key),
                            'secret_key' => $this->encryptedOrNull($gateway->secret_key),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('payment_gateways')
            ->select(['id', 'api_key', 'secret_key'])
            ->orderBy('id')
            ->chunkById(100, function ($gateways) {
                foreach ($gateways as $gateway) {
                    DB::table('payment_gateways')
                        ->where('id', $gateway->id)
                        ->update([
                            'api_key' => $this->decryptedOrSame($gateway->api_key),
                            'secret_key' => $this->decryptedOrSame($gateway->secret_key),
                        ]);
                }
            });
    }

    private function encryptedOrNull(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (DecryptException) {
            return Crypt::encryptString($value);
        }
    }

    private function decryptedOrSame(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }
};
