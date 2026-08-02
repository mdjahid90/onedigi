<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class RefundPolicyController extends Controller
{
    public function __invoke(): View
    {
        $policy = Setting::getValue('refund_policy', '');

        return view('pages.refund-policy', compact('policy'));
    }
}
