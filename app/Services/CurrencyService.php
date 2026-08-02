<?php

namespace App\Services;

use App\Models\Setting;

class CurrencyService
{
    /**
     * Base prices in the database are assumed to be BDT.
     */
    public const BASE = 'BDT';

    /**
     * @return array<int, string>
     */
    public function supported(): array
    {
        return ['BDT', 'USD', 'RUB'];
    }

    public function code(): string
    {
        $code = (string) session('currency', request()->cookie('currency', self::BASE));
        $code = strtoupper($code);

        if (!in_array($code, $this->supported(), true)) {
            return self::BASE;
        }

        if ($code === 'USD') {
            $usd = (float) Setting::getValue('currency_usd_rate_bdt', '0');
            return $usd > 0 ? 'USD' : self::BASE;
        }

        if ($code === 'RUB') {
            $rub = (float) Setting::getValue('currency_rub_rate_bdt', '0');
            return $rub > 0 ? 'RUB' : self::BASE;
        }

        return self::BASE;
    }

    public function symbol(string $code = null): string
    {
        $c = $code ? strtoupper($code) : $this->code();

        return match ($c) {
            'USD' => '$',
            'RUB' => '₽',
            default => '৳',
        };
    }

    public function symbolPosition(string $code = null): string
    {
        $c = $code ? strtoupper($code) : $this->code();

        return match ($c) {
            'USD' => 'prefix',
            default => 'suffix',
        };
    }

    /**
     * Rates are stored as: how many BDT equals 1 unit of the target currency.
     * Example: if 1 USD = 110 BDT, store 110.
     */
    public function bdtPerUnit(string $code = null): float
    {
        $c = $code ? strtoupper($code) : $this->code();

        if ($c === 'USD') {
            return (float) Setting::getValue('currency_usd_rate_bdt', '0');
        }

        if ($c === 'RUB') {
            return (float) Setting::getValue('currency_rub_rate_bdt', '0');
        }

        return 1.0;
    }

    /**
     * Multiply a BDT amount by this factor to get the selected currency.
     */
    public function factor(string $code = null): float
    {
        $c = $code ? strtoupper($code) : $this->code();
        if ($c === self::BASE) {
            return 1.0;
        }

        $bdtPer = $this->bdtPerUnit($c);
        if ($bdtPer <= 0) {
            return 1.0;
        }

        return 1.0 / $bdtPer;
    }

    public function convertFromBdt(float $amountBdt, string $code = null): float
    {
        return $amountBdt * $this->factor($code);
    }

    public function format(float $amountBdt, bool $withSymbol = true, string $code = null): string
    {
        $c = $code ? strtoupper($code) : $this->code();
        $converted = $this->convertFromBdt($amountBdt, $c);
        $number = number_format($converted, 2, '.', ',');
        $number = rtrim(rtrim($number, '0'), '.');

        if (!$withSymbol) {
            return $number;
        }

        $symbol = $this->symbol($c);
        $pos = $this->symbolPosition($c);

        return $pos === 'prefix' ? ($symbol . $number) : ($number . $symbol);
    }
}
