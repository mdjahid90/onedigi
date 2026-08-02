<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));
        $perPage = max(5, min(50, (int) $request->integer('per_page', 8)));

        $query = Transaction::query()
            ->with('order')
            ->latest();

        if ($status !== 'all') {
            $statusMap = [
                'completed' => ['COMPLETED'],
                'pending' => ['PENDING', 'CREATED', 'INITIATED'],
                'refunded' => ['REFUNDED'],
                'canceled' => ['CANCELLED', 'FAILED', 'CHARGEBACK'],
            ];

            if (isset($statusMap[$status])) {
                $query->whereIn('status', $statusMap[$status]);
            }
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('gateway', 'like', '%' . $search . '%')
                    ->orWhere('trx_id', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery
                            ->where('customer_name', 'like', '%' . $search . '%')
                            ->orWhere('customer_email', 'like', '%' . $search . '%');
                    });
            });
        }

        $transactions = $query->paginate($perPage)->withQueryString();

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }
}
